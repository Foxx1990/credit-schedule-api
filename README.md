Credit Schedule API
Credit Schedule API is a RESTful API built with Symfony that provides endpoints to calculate loan repayment schedules with fixed installments. It includes functionality for user authentication via JWT, listing and excluding specific calculations, and more.

Prerequisites
Before running the API, ensure you have the following installed on your machine:

PHP >= 8.0
Composer
MySQL or another supported database
OpenSSL (for JWT key generation)
Installation
1. Clone the repository

git clone 
cd credit-schedule-api

2. Install dependencies

composer install

3. Configure environment variables
Create the .env.local file to configure your database and JWT settings.


cp .env .env.local
Update the following values in .env.local:


DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase

4. Generate JWT keys

mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
Set the JWT_PASSPHRASE in your .env.local to match the passphrase used during the key generation.

5. Set up the database

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

6. Start the development server

symfony server:start
API Endpoints
Authentication
POST /api/login
Generates a JWT token for authenticated users.

Request:


{
    "username": "your_username",
    "password": "your_password"
}

Response:


{
    "token": "jwt_token"
}
Use this token for further API calls by including it in the Authorization header as Bearer <token>.

Loan Calculation

GET /api/calculate
Calculates the loan repayment schedule based on the provided loan amount, number of installments, and interest rate.

Request:

amount: Loan amount (valid range: 1000-12000, multiples of 500)
num_installments: Number of installments (valid range: 3-18, multiples of 3)
interest_rate: Interest rate as a decimal (e.g., 0.05 for 5%)
Example URL:


GET /api/calculate?amount=5000&num_installments=12&interest_rate=0.05
Response:


{
    "calculationDate": "2024-09-09T12:34:56",
    "amount": 5000,
    "numInstallments": 12,
    "interestRate": 0.05,
    "schedule": [
        {
            "installmentNumber": 1,
            "installmentAmount": 450,
            "interest": 200,
            "capital": 250
        },
        ...
    ]
}
Excluding a Calculation
POST /api/exclude/{id}
Excludes a calculation from the list. This action is protected by JWT.

Request:

Header: Authorization: Bearer <token>
Example:


POST /api/exclude/1
Response:


{
    "status": "Calculation excluded"
}
Listing Recent Calculations
GET /api/schedules
Lists the 4 most recent calculations. This action is protected by JWT.

Request:

Header: Authorization: Bearer <token>
Example:


GET /api/schedules
Response:

json

[
    {
        "calculationId": 1,
        "calculationDate": "2024-09-09T12:34:56",
        "amount": 5000,
        "numInstallments": 12,
        "interestRate": 0.05,
        "totalInterest": 600
    },
    ...
]

