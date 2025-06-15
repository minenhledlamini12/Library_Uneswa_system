#include <WiFi.h>
#include <WebServer.h>
#include <LiquidCrystal_I2C.h>

// WiFi credentials
const char* ssid = "Tema";
const char* password = "Tema1234";

// Static IP config for EXIT ESP32
IPAddress local_IP(192, 168, 15, 149);  // <-- CHANGE TO 192.168.15.149
IPAddress gateway(192, 168, 15, 1);
IPAddress subnet(255, 255, 255, 0);



// Motor pins
const int IN1 = 12;
const int IN2 = 14;
const int ENA = 13;  // Motor enable pin (digital ON/OFF)

// Touch sensor and LED pins
const int touchPin = 4;  // Touch sensor input pin
const int ledPin = 5;    // LED output pin (indicator LED)

// LCD setup
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Web server
WebServer server(80);

// Timing parameters
const unsigned long doorOpenTime = 5000;    // 5 seconds door open
const unsigned long doorCloseTime = 5000;   // 5 seconds door close
const unsigned long thankYouTime = 3000;    // 3 seconds thank you display
const unsigned long toggleInterval = 3000;  // 3 seconds toggle exit/scan prompt

// Display states
enum DisplayState {
  WELCOME_SCAN_ALTERNATE,
  SHOW_SCANNING,
  SHOW_ACCESS_GRANTED,
  SHOW_ACCESS_DENIED,
  SHOW_THANK_YOU,
  SHOW_TOUCH_WAIT
};

DisplayState currentDisplay = WELCOME_SCAN_ALTERNATE;
unsigned long lastToggleTime = 0;

// Variables for scanned data and user info
String scannedData = "";
String accessUser = "";

// Door control variables
bool doorOpening = false;
unsigned long doorActionStartTime = 0;

// Touch sensor state
bool touchRequested = false;
bool ledState = false;  // track LED state

// Door state machine
enum DoorState {
  DOOR_IDLE,
  DOOR_OPENING,
  DOOR_CLOSING,
  DOOR_DONE
};
DoorState doorState = DOOR_IDLE;

void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA);

  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(ENA, OUTPUT);

  pinMode(touchPin, INPUT);
  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, LOW);  // LED off initially

  lcd.init();
  lcd.backlight();

  // Configure static IP (optional)
  if (!WiFi.config(local_IP, gateway, subnet)) {
    Serial.println("Failed to configure static IP");
  }

  WiFi.begin(ssid, password);
  lcd.clear();
  lcd.print("Connecting WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());

  lcd.clear();

  // Setup HTTP routes
  server.on("/scanning", HTTP_GET, handleScanning);
  server.on("/access-granted", HTTP_GET, handleAccessGranted);
  server.on("/access-denied", HTTP_GET, handleAccessDenied);

  // Endpoints for LED control and door open
  server.on("/open_door", HTTP_GET, handleOpenDoor);
  server.on("/turn_on_led", HTTP_GET, handleTurnOnLed);
  server.on("/turn_off_led", HTTP_GET, handleTurnOffLed);

  // New endpoint: open door and turn off LED
  server.on("/open_door_and_turn_off_led", HTTP_GET, handleOpenDoorAndTurnOffLed);

  server.begin();
  Serial.println("HTTP server started");

  // Start with exit/scan alternating display
  currentDisplay = WELCOME_SCAN_ALTERNATE;
  lastToggleTime = millis();
  updateLCD();
}

void loop() {
  // Read touch sensor
  int touchState = digitalRead(touchPin);

  if (touchState == HIGH) {  // Adjust if your sensor is active LOW
    if (!ledState) { // prevent overriding remote LED control
      digitalWrite(ledPin, HIGH);
      ledState = true;
    }

    if (!touchRequested) {
      touchRequested = true;
      Serial.println("Touch sensor activated - waiting for librarian");
      currentDisplay = SHOW_TOUCH_WAIT;
      updateLCD();
    }
  } else if (!ledState) { // only turn off LED if remote control not active
    digitalWrite(ledPin, LOW);
    if (touchRequested) {
      touchRequested = false;
      if (!doorOpening) {
        currentDisplay = WELCOME_SCAN_ALTERNATE;
        lastToggleTime = millis();
        updateLCD();
      }
    }
  }

  server.handleClient();

  // Door open/close logic (state machine)
  if (doorOpening) {
    unsigned long now = millis();
    switch (doorState) {
      case DOOR_OPENING:
        if (now - doorActionStartTime < doorOpenTime) {
          // Run motor forward (open)
          digitalWrite(IN1, HIGH);
          digitalWrite(IN2, LOW);
          digitalWrite(ENA, HIGH);
        } else {
          // Start closing
          doorActionStartTime = now;
          doorState = DOOR_CLOSING;
        }
        break;
      case DOOR_CLOSING:
        if (now - doorActionStartTime < doorCloseTime) {
          // Run motor reverse (close)
          digitalWrite(IN1, LOW);
          digitalWrite(IN2, HIGH);
          digitalWrite(ENA, HIGH);
        } else {
          stopMotor();
          doorActionStartTime = now;
          doorState = DOOR_DONE;
          currentDisplay = SHOW_THANK_YOU;
          updateLCD();
        }
        break;
      case DOOR_DONE:
        if (now - doorActionStartTime > thankYouTime) {
          doorOpening = false;
          doorState = DOOR_IDLE;
          currentDisplay = WELCOME_SCAN_ALTERNATE;
          lastToggleTime = millis();
          updateLCD();
        }
        break;
      default:
        break;
    }
  }

  // Alternate exit and scan prompt when idle and no touch request
  if (!doorOpening && !touchRequested && currentDisplay == WELCOME_SCAN_ALTERNATE) {
    if (millis() - lastToggleTime > toggleInterval) {
      toggleWelcomeScan();
      lastToggleTime = millis();
    }
  }
}

// HTTP handler: scanning in progress
void handleScanning() {
  if (server.hasArg("data")) {
    scannedData = server.arg("data");
    Serial.println("Scanning data: " + scannedData);
    currentDisplay = SHOW_SCANNING;
    updateLCD();
    server.sendHeader("Access-Control-Allow-Origin", "*");
    server.send(200, "text/plain", "Scanning displayed");
  } else {
    server.sendHeader("Access-Control-Allow-Origin", "*");
    server.send(400, "text/plain", "Missing data parameter");
  }
}

// HTTP handler: access granted
void handleAccessGranted() {
  if (server.hasArg("user")) {
    accessUser = server.arg("user");
  } else {
    accessUser = "Guest";
  }
  Serial.println("Exit granted for user: " + accessUser);

  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "text/plain", "Exit door opening started");

  doorOpening = true;
  doorActionStartTime = millis();
  doorState = DOOR_OPENING;

  currentDisplay = SHOW_ACCESS_GRANTED;
  updateLCD();
}

// HTTP handler: access denied
void handleAccessDenied() {
  Serial.println("Exit denied");

  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "text/plain", "Exit denied displayed");

  currentDisplay = SHOW_ACCESS_DENIED;
  updateLCD();

  unsigned long start = millis();
  while (millis() - start < 3000) {
    server.handleClient();
    delay(10);
  }

  currentDisplay = WELCOME_SCAN_ALTERNATE;
  lastToggleTime = millis();
  updateLCD();
}

// HTTP handler: open door remotely
void handleOpenDoor() {
  Serial.println("Remote exit open");
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "text/plain", "Exit door opening started");
  doorOpening = true;
  doorActionStartTime = millis();
  doorState = DOOR_OPENING;
  currentDisplay = SHOW_ACCESS_GRANTED;
  accessUser = "Remote";
  updateLCD();
}

// HTTP handler: turn on LED remotely
void handleTurnOnLed() {
  Serial.println("Remote LED ON");
  digitalWrite(ledPin, HIGH);
  ledState = true;
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "text/plain", "LED turned ON");
}

// HTTP handler: turn off LED remotely
void handleTurnOffLed() {
  Serial.println("Remote LED OFF");
  digitalWrite(ledPin, LOW);
  ledState = false;
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "text/plain", "LED turned OFF");
}

// HTTP handler: open door AND turn off LED remotely
void handleOpenDoorAndTurnOffLed() {
  Serial.println("Remote:open exit, turn off LED");
  digitalWrite(ledPin, LOW); // Turn off LED
  ledState = false;
  doorOpening = true;
  doorActionStartTime = millis();
  doorState = DOOR_OPENING;
  currentDisplay = SHOW_ACCESS_GRANTED;
  accessUser = "Remote";
  updateLCD();
  server.sendHeader("Access-Control-Allow-Origin", "*");
  server.send(200, "text/plain", "LED turned off, exit door opening started");
}

// Motor control functions
void stopMotor() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(ENA, LOW);
}

// Toggle exit and scan prompt on LCD
void toggleWelcomeScan() {
  static bool showExit = true;
  lcd.clear();
  if (showExit) {
    lcd.setCursor(0, 0);
    lcd.print("Thank you for");
    lcd.setCursor(0, 1);
    lcd.print("visiting Uneswa");
  } else {
    lcd.setCursor(0, 0);
    lcd.print("Scan to EXIT");
  }
  showExit = !showExit;
}

// Update LCD according to current display state
void updateLCD() {
  lcd.clear();
  switch (currentDisplay) {
    case WELCOME_SCAN_ALTERNATE:
      toggleWelcomeScan();
      break;
    case SHOW_SCANNING:
      lcd.setCursor(0, 0);
      lcd.print("Scanning Exit:");
      lcd.setCursor(0, 1);
      lcd.print(scannedData.length() > 16 ? scannedData.substring(0, 16) : scannedData);
      break;
    case SHOW_ACCESS_GRANTED:
      lcd.setCursor(0, 0);
      lcd.print("Exit Granted");
      lcd.setCursor(0, 1);
      lcd.print("Goodbye:");
      lcd.print(accessUser.length() > 10 ? accessUser.substring(0, 10) : accessUser);
      break;
    case SHOW_ACCESS_DENIED:
      lcd.setCursor(0, 0);
      lcd.print("Exit Denied");
      lcd.setCursor(0, 1);
      lcd.print("No Entry Found");
      break;
    case SHOW_THANK_YOU:
      lcd.setCursor(0, 0);
      lcd.print("Visit Logged");
      break;
    case SHOW_TOUCH_WAIT:
      lcd.setCursor(0, 0);
      lcd.print("Wait for librarian");
      lcd.setCursor(0, 1);
      lcd.print("to open EXIT");
      break;
  }
}
