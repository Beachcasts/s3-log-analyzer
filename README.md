# Beachcasts/S3-Log-Analyzer

PHP project using [Mezzio](https://docs.mezzio.dev/) downloads usage logs from S3, parse them, and add them to a MySQL database. Following addition to the database, stats pages show usage data and charts with results graphically using Google Charts.

> NOTE: This application currently is untested, but works. I intend to return and do that, but it's not done yet. Pull requests welcomed.

# Setup Steps

Clone the [Beachcasts/S3-Log-Analyzer](https://github.com/Beachcasts/s3-log-analyzer) repo from Github. In your terminal, navigate to the new directory.

## Composer

Install dependencies using Composer.

```bash
composer install
```

## Database

Create a new MySQL database by using statements in `/data/schema.sql` to create the database and table.

## Environment

Rename `.env.dist` to become `.env`, and update as needed with your AWS account (IAM) settings.

> NOTE: The bucket should be the location of the stats, not necessarily the bucket you want stats on. In AWS you will need to activate the stats for a given bucket, and inform where to store them. In my case, I store the stats in a bucket named 'stats-bucket'.

Rename `/config/autoload/db.local.php.dist` to become `/config/autoload/db.local.php`, and update as needed for your database credentials and location.

## Run

At this point you should have a "working" application ready to serve. There are many ways to accomplish this. Here are some examples:

* Using the built-in PHP server
```bash
php -S localhost:8080 -t public/
```

* Upload the application to a webserver, or running locally, as with any PHP web-based application.

* Deploy to AWS Lambda as an HTTP function

    This option can can be easily accomplished by installing [Serverless](), and issuing the following command to deploy to Lambda.
    
    Update the contents of `serverless.yml` by adding the bucket ARN from AWS. Replace `<stats-bucket>` with your bucket name. You may also need to update the AWS region.
    
    Also, some of the variables in `.env` may not be needed if deployed this way, because they are already handled by `serverless.yml`.
    
```bash
serverless deploy
```

> NOTE: If deploying to Lambda, you will need to update the menu in `/src/App/templates/layout/default.html` according to the URL provided by Lambda.
