### Key Application Endpoints

* API collection documentation:https://documenter.getpostman.com/view/6464771/2sA2xe4ZHq
* API baseUrl: http://localhost:8000/api/v1/


# Project setup guidelines

1. Clone the project from the repo, and run the following commands to setup the project environment locally:

* composer install
* cp .env.example .env or copy .env.example .env
* php artisan key:generate
* php artisan migrate
* php artisan db:seed
* php artisan serve
* php artisan queue:work
  
2. Configure the ".env" to reflect your database credentials as shown below:

* DB_CONNECTION=mysql
* DB_HOST=127.0.0.1
* DB_PORT=3306
* DB_DATABASE=YOUR_DATABASE_NAME
* DB_USERNAME=YOUR_DATABASE_USERNAME
* DB_PASSWORD=YOUR_DATABASE_PASSWORD

3. To use Redis cache and queue, configure the ".env" file as shown below:

* CACHE_DRIVER=redis
* QUEUE_CONNECTION=redis
* SESSION_DRIVER=redis

* REDIS_HOST=127.0.0.1
* REDIS_PASSWORD=null
* REDIS_PORT=6379
* REDIS_CLIENT=predis

4. Optionally, to receive account verification OTP via email you can configure the ".env" file with your SMTP credentials as shown below:

* MAIL_MAILER=smtp
* MAIL_HOST=smtp.mailtrap.io
* MAIL_PORT=2525
* MAIL_USERNAME=username
* MAIL_PASSWORD=password
* MAIL_ENCRYPTION=tls
* MAIL_FROM_ADDRESS="hello@example.com"
* MAIL_FROM_NAME="${APP_NAME}"

### NB: You can also skip step(4) above, but you will have to check the "/storage/logs/laravel.log" file for your account verification OTP which is only valid for ten(10) minutes.


4. Test cases could be executed as a whole or as individual function of each case. As shown below each individual test case function can be tested:

### RegistrationTest: Run the following command:
- php artisan test --filter RegistrationTest::test_signup_with_valid_payload
- php artisan test --filter RegistrationTest::test_signup_with_unmatched_password_and_confirmPassword

### LoginTest: Run the following command:
- php artisan test --filter LoginTest::test_verified_user_account_login_with_the_valid_email_and_password
- php artisan test --filter LoginTest::test_unverified_user_account_login_with_invalid_password

### ProductTest: Run the following command:
- php artisan test --filter ProductTest::test_verified_user_fetch_product_list
- php artisan test --filter ProductTest::test_verified_user_fetch_product_by_id
- php artisan test --filter ProductTest::test_unverified_user_fetch_product_list


# Application workflow summary
1. Signup
2. Account verification OTP will be sent to your email account. You can also check the "/storage/logs/laravel.log" file for the OTP if you wishes not to use SMTP.
3. Enter the sent OTP to verify your account, as only verified account will be allowed to login.
4. You can RESEND the OTP if the time(10 minutes) elapse and you are yet to verify your account.
5. Login.
6  Create product.
7. Retrieve all created products.
8. Fetch a single product.
9. Update a selected product.
10. Update product category.
11. Delete selected product.
12. Fetch list of statuses.
13. Fetch list of available categories.

NB: 
1. Kindly note that you can only update/delete a product created by you.
2. You need to supply your access token to for all product related route

### Key Application Endpoints

* API collection documentation:nhttps://documenter.getpostman.com/view/6464771/2sA2xe4ZHq
* API baseUrl: http://localhost:8000/api/v1/
