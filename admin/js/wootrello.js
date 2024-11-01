// multi select init Below.
document.multiselect('.metaDropDown');

// Showing Warning message 
document.querySelectorAll('.trelloBoard, .trello_list, .trello_due_date, .trello_label_colour').forEach(element => {
    element.addEventListener('click', function () {
        const trelloAccessCode = document.querySelector('#trello_api').value;
        if (!trelloAccessCode) {
            document.querySelector('#save_settings').style.borderColor = 'red';
            document.querySelector('#trello_api').style.borderColor = 'red';
            alert('Please save Trello access code, before selecting the Trello Board or List!!');
        }
    });
});

// Getting Trello Board lists 
document.querySelectorAll('.trelloBoard').forEach(element => {
    element.addEventListener('change', async function (e) {
        // Getting list ID
        const listFieldsID = `#${e.target.id}_trello_list`;
        // Getting Trello Board ID aka Value
        const value = e.target.value;
        // Checking if Value
        if (value) {
            // Disable The Trello List fields
            document.querySelector(listFieldsID).disabled = true;
            // JS object for sending the data to the server
            const ajaxData = {
                action: 'wootrello_ajax_response',
                boardID: value,
                security: wootrello_data.security
            };

            try {
                // AJAX call
                const response = await fetch(wootrello_data.wootrelloAjaxURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(ajaxData)
                });

                const trelloBoardList = await response.json();

                // If Bool is True, Then Proceed
                if (trelloBoardList[0]) {
                    // Enable The Trello List fields & Populate the Fields
                    const listField = document.querySelector(listFieldsID);
                    listField.disabled = false;
                    // Emptying
                    listField.innerHTML = '<option value=""> -- Select Trello List -- </option>';
                    // Appending to the Dropdown Select
                    Object.entries(trelloBoardList[1]).forEach(([key, value]) => {
                        listField.insertAdjacentHTML('beforeend', `<option value="${key}">${value}</option>`);
                    });
                } else {
                    alert(`ERROR : ${trelloBoardList[1]}`);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });
});

// Single Order Page
if (document.getElementById('wootrelloCreateCard')) {
    document.getElementById('wootrelloCreateCard').addEventListener('click', async function () {
        const wooTrelloRelSettings = document.getElementById('wooTrelloRelSettings').value;
        const wootrelloOrderId = document.getElementById('wootrello_order_id').value;
        const wootrelloNonce = document.getElementById('wootrello_nonce').value;
        // If wooTrelloRelSettings and orderID are not empty
        if (wooTrelloRelSettings && wootrelloOrderId) {
            const ajaxDataTwo = {
                action: 'wootrello_ajax_single_order',
                orderID: wootrelloOrderId,
                relatedSettings: wooTrelloRelSettings,
                security: wootrelloNonce
            };
            // 
            try {
                // AJAX request
                const response = await fetch(wootrello_data.wootrelloAjaxURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(ajaxDataTwo)
                });

                const wootrelloResponse = await response.json();
                console.log(wootrelloResponse);
            } catch (error) {
                console.error('Error:', error);
            }
        } else {
            alert('Please select the Dropdown! or order ID is Empty!');
        }
    });
}

// Deleting Single order Trello History
if (document.getElementById('wooTrelloDeleteHistory')) {
    document.getElementById('wooTrelloDeleteHistory').addEventListener('click', async function () {
        const wootrelloOrderId = document.getElementById('wootrello_order_id').value;
        const wootrelloNonce = document.getElementById('wootrello_nonce').value;
        // 
        if (wootrelloOrderId && wootrelloNonce) {
            const deleteData = {
                action: 'wootrello_ajax_delete_history',
                orderID: wootrelloOrderId,
                security: wootrelloNonce
            };

            try {
                const response = await fetch(wootrello_data.wootrelloAjaxURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(deleteData)
                });

                const deleteResponse = await response.json();
                console.log(deleteResponse);
                document.getElementById('trelloHistoryContent').style.display = 'none';
            } catch (error) {
                console.error('Error:', error);
            }
        } else {
            alert('ID or Nonce not Empty.');
        }
    });
}

