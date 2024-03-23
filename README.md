## About Larashop

This project developed using TTD (Test Driven Development)

### Models Included

-   User (For authentication)
-   Role (For authorization)
-   Item (Business entity)
-   Order (Business entity)
-   Payment (Business entity)
-   Adress (Business entity)
-   Picture (Business entity)

### Brief Description

#### User

-   User can sign up via email and all authentication related activities are available via the email.
-   User can make an order which includes the item(s).
-   User can cancel or pay the pending order.
-   User can update the item of a pending order
-   User can view the orders owned by the user
-   User can view the items and put them to an order
-   User can upload picture
-   User can download receipt(PDF)
-   User can check owned orders report

#### Admin

-   Admin can list and manage the items (Create, Retrieve, Update, Delete, Upload Picture)
-   Admin can manage the order status
-   Admin can list and manage the users
-   Admin can check orders report

### Technical Features

-   REST api
-   Email notification
-   Broadcasting using laravel reverb
-   File Storage using S3 (Minio)
-   Scheduling and Queueing(database driver)
-   Environment variable management using encryption and commands
-   Testing (PHP unit)
-   Polymophic relationships and Piviot table model
-   PaymentService interface
-   Policy for authorization
