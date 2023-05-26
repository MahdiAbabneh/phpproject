<?php

include "connect.php";

// Validate email
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number
function validatePhoneNumber($phoneNumber)
{
    return preg_match('/^[0-9]+$/', $phoneNumber);
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $action = $requestData['action'];

    // Add a new contact
    if ($action === 'add') {
        $firstName = $requestData['first_name'];
        $lastName = $requestData['last_name'];
        $email = $requestData['email'];
        $phoneNumber = $requestData['phone_number'];

        if (!validateEmail($email)) {
            $response = array('message' => 'Invalid email format');
            echo json_encode($response);
            exit();
        }

        if (!validatePhoneNumber($phoneNumber)) {
            $response = array('message' => 'Invalid phone number format');
            echo json_encode($response);
            exit();
        }

        // Insert new contact
        $sql = "INSERT INTO contacts (first_name, last_name, email, phone_number) VALUES ('$firstName', '$lastName', '$email', '$phoneNumber')";
        if ($con->query($sql)->rowCount() > 0) {
            $response = array('message' => 'Contact added successfully');
            echo json_encode($response);
        } else {
            $response = array('message' => 'Error adding contact: ');
            echo json_encode($response);
        }
    }

    // Edit a contact
    if ($action === 'edit') {
        $contactId = $requestData['id'];
        $firstName = $requestData['first_name'];
        $lastName = $requestData['last_name'];
        $email = $requestData['email'];
        $phoneNumber = $requestData['phone_number'];

        if (!validateEmail($email)) {
            $response = array('message' => 'Invalid email format');
            echo json_encode($response);
            exit();
        }

        if (!validatePhoneNumber($phoneNumber)) {
            $response = array('message' => 'Invalid phone number format');
            echo json_encode($response);
            exit();
        }

        // Check if contact exists
        $sql = "SELECT * FROM contacts WHERE id = $contactId";
        $result = $con->query($sql);
        if ($result->rowCount() === 0) {
            $response = array('message' => 'Contact not found');
            echo json_encode($response);
            exit();
        }

        // Update contact
        $sql = "UPDATE contacts SET first_name = '$firstName', last_name = '$lastName', email = '$email', phone_number = '$phoneNumber' WHERE id = $contactId";
        if ($con->query($sql)->rowCount() > 0) {
            $response = array('message' => 'Contact updated successfully');
            echo json_encode($response);
        } else {
            $response = array('message' => 'Error updating contact: ');
            echo json_encode($response);
        }
    }

    // Delete a contact
    if ($action === 'delete') {
        $contactId = $requestData['id'];

        // Check if contact exists
        $sql = "SELECT * FROM contacts WHERE id = $contactId";
        $result = $con->query($sql);
        if ($result->rowCount() === 0) {
            $response = array('message' => 'Contact not found');
            echo json_encode($response);
            exit();
        }

        $sql = "DELETE FROM contacts WHERE id = $contactId";
        if ($con->query($sql)->rowCount() > 0) {
            $response = array('message' => 'Contact deleted successfully');
            echo json_encode($response);
        } else {
            $response = array('message' => 'Error deleting contact: ');
            echo json_encode($response);
        }
    }

    if ($action === 'deleteAll') {
        // Delete all contacts from the database
        $sql = "DELETE FROM contacts";
        if ($con->query($sql)->rowCount() > 0) {
            $resetSql = "ALTER TABLE contacts AUTO_INCREMENT = 1";
            $con->query($resetSql);
            
            $response = array('message' => 'All contacts deleted successfully');
            echo json_encode($response);
        } else {
            $response = array('message' => 'Error deleting contacts');
            echo json_encode($response);
        }
    }
}

// Get contact data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM contacts";
    $result = $con->query($sql);

    if ($result->rowCount() > 0) {
        $contacts = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $contacts[] = $row;
        }
        $response = array('contacts' => $contacts);
        echo json_encode($response);
    } else {
        $response = array('contacts' => []);
        echo json_encode($response);
    }
}

$con = null;

?>
