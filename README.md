# CSE-391-Assignment-3

# Car Workshop Appointment System

A web-based appointment system for a car workshop, allowing clients to book appointments with their desired mechanics and admins to manage all appointments.

## Features

### User Panel
- Register and log in as a client
- Book a car service appointment by providing:
  - Name
  - Address
  - Phone
  - Car License Number
  - Car Engine Number
  - Appointment Date
  - Select a mechanic (shows available slots)
- Prevents double booking and mechanic overbooking
- View and manage your appointments (if implemented)

### Admin Panel
- Log in as admin
- View all appointments with client and car details
- Change appointment date and assigned mechanic
- Delete appointments
- See mechanic availability

## Technologies Used
- PHP (backend)
- MySQL (database)
- HTML, CSS, JavaScript (frontend)
- Responsive design

## Setup Instructions

1. **Clone or Download the Repository**
   - Place the project folder in your XAMPP `htdocs` directory.

2. **Start XAMPP Services**
   - Start Apache and MySQL from the XAMPP Control Panel.

3. **Create the Database**
   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Import the `database.sql` file provided in the project to create all tables and insert sample data (including a default admin and mechanics).

4. **Configure Database Connection**
   - Check `config.php` for database credentials. Default is:
     - Host: `localhost`
     - User: `root`
     - Password: (empty)
     - Database: `car_workshop`

5. **Access the Application**
   - User Panel: [http://localhost/Car_appointment/index.php](http://localhost/Car_appointment/index.php)
   - Admin Panel: [http://localhost/Car_appointment/admin.php](http://localhost/Car_appointment/admin.php)

## Default Admin Credentials
- **Username:** `admin`
- **Password:** `password`

## Usage

### For Users
1. Register for a new account or log in if you already have one.
2. Book an appointment by filling out the form and selecting a mechanic with available slots.
3. You will be notified if you try to double book or if a mechanic is fully booked.

### For Admins
1. Log in using the admin credentials.
2. View all appointments, change dates, assign mechanics, or delete appointments as needed.
3. Only admins can access the admin panel and manage all appointments.

## Notes
- Each mechanic can be assigned to a maximum of 4 cars per day.
- Clients cannot book more than one appointment per day.
- The system prevents overbooking and double booking.
- User and admin sessions are managed separately; both can be logged in at the same time in the same browser.

## Customization
- You can add more mechanics or admins via phpMyAdmin or by extending the admin panel.
- To add more admin pages, update the `$adminPages` array in `includes/navbar.php`.

## License
This project is for educational purposes. 
