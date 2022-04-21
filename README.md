# patientportal

Patient Portal for ORHC

This is a website that patients can use to pass information remotely to cardiologists. Has a fully functioning patient account system and can intake patient health information, such as jugular venous pressure and medical history. Some things are still a work in progress (such as a fully fleshed-out admin system).

Before you run the project, you need to have the database and tables constructed. Make sure you have PHP and MySQL installed and running in some way (such as with an all-in-one package, like XAMPP). The manager.php file will construct the database and tables if they do not exist, so run that file first. manager.php will also create an admin account.

Admin and patient profiles need to be made manually. create-admin.php requires admin permissions to access.