<!-- resources/views/emails/approval_reminder.blade.php -->

<!DOCTYPE html>
<html>
    <head>
        <title>Entity Approval Reminder</title>
        <!-- Add Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card col-md-8 mx-auto">
                <div class="card-body">
                    <p>This is a UAT Test Approval Reminder</p>
                    <p><b>Hi {{ $username }},</b></p>
                    <p>You have <b>{{ $count }}</b> entities that are pending for your Approval</p>
                    <p>Please review them at your earliest convenience.</p>
                </div>
            </div>
        </div>
        <!-- Add Bootstrap JS and dependencies -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
</html>



