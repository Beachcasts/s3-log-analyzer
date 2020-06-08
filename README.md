# Beachcasts/S3-Log-Analyzer

# Setup Steps

Start the project

```bash
composer create project mezzio
```

Require Bref

```bash
composer require bref/bref
```

Initialize Bref

```bash
vendor/bin/bref init
```

Require Flysystem for AWS

```bash
composer require league/flysystem-aws-s3-v3
```

Require Doctrine Dbal

```bash
composer require doctrine/dbal
```

Deploy to AWS Lambda using Serverless

```bash
sls deploy
```
