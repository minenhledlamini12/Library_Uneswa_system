   function getBookDetails(isbn) {
         fetch('get_book_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'bookISBN=' + encodeURIComponent(isbn)
            })
             .then(response => {
                  if (!response.ok) {
                  throw new Error(`HTTP error! Status: ${response.status}`);
                  }
                 return response.json();
                })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('bookTitle').value = data.book.Title;
                    document.getElementById('bookAuthor').value = data.book.Author;
                    document.getElementById('bookDetails').style.display = 'block';
                } else {
                    alert('Book not found.');
                    document.getElementById('bookDetails').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error fetching book details: ' + error);
            });
        }

                function getMemberDetails(memberType) {
                    if (memberType === "") {
                        alert("Please select a member category.");
                        return;
                    }
                    document.getElementById('memberType').value = memberType;
                    fetch('get_member_details.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'memberType=' + encodeURIComponent(memberType)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('memberType').value = memberType;
                            document.getElementById('memberEmail').value = data.data.Email;
                            document.getElementById('borrowerName').value = data.data.Name;
                            document.getElementById('borrowerEmail').value = data.data.Email;


                         // Calculate return date based on member type
                         var borrowDate = document.getElementById('borrowDate').value;
                            if (!borrowDate) {
                                alert("Please select a borrow date.");
                                return;
                            }

                            let returnDate = calculateReturnDate(borrowDate, memberType);
                            document.getElementById('returnDate').value = returnDate;

                        } else {
                           alert('Error fetching member details: ' + data.message);
                            document.getElementById('memberType').value = "";
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching member details.');
                    });
                }
