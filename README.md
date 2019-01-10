# Vegas Card - Sauron T.A.S.E
[![N|Solid](https://vegascard.com.br/images/powered-by.png)](https://nodesource.com/products/nsolid)

Vegas Card Sauron T.A.S.E is a software that monitors Vegas Card environment and it sends SMS when it finds an error.

## Getting Started

### Prerequisites

* [Ubuntu Server](https://www.ubuntu.com/download/server) - Ubuntu Server 18 LTS
* [PHP](http://php.net/downloads.php) - PHP 7.2
* [pthreads](https://github.com/krakjoe/pthreads) - multi-threading pthreads v3
* sockets - active on php extensions

### Clone
* Clone this repo to your local machine using https://github.com/rafaelhirooka/sauronTASE.git

### Install dependencies
```
$ composer install
```

### Setup
* Edit where the logs will be registered editing
```
    .
    ├── src
        ├── config
            ├── db.php
```

* Edit where is sms sender service
```
    .
    ├── src
        ├── config
            ├── sms.php
```

* Edit aws configurations
```
    .
    ├── src
        ├── config
            ├── aws.php
```

### Start
* Run on terminal
```
$ php sauron run
```

* If you want to run as a linux service (recommended)
```
$ php sauron run as-service
```

## Create a new program to monitor

### Add a new program
* If you want to create a new program to monitors eg. Zabbix, you'll need to create a new program and fill the main() method. 
* All programs extends AbstractProgram.
* When the program has been created, it will be in src/app/Programs. Open it and write the main() method.

```
$ php sauron create-program ProgramName
```

### Include program
* After you create the program and fill main() method, you need to include it
```
$ php sauron include-program ProgramName
```

### Restart service
* Now you need to restart Sauron T.A.S.E service
```
$ kill pid
$ php sauron run
```

## Logs
* The logs are logged on MongoDB.

* Database name is sauron-tase

* The Sauron register logs in 2 levels:
```
Info - register info like "sent a message to xxx using aws", "healer restart some program"
Error - register errors 
```


## Running the tests

We have PHPUnit tests. To run then, just:

```
$ composer run test
```

## Diagrams

### Schema
![N|Solid](https://vegascard.com.br/images/monitoring-system.jpg)

### Class diagram
![N|Solid](https://vegascard.com.br/images/monitoring-system-class.jpg)



## Authors

* **Rafael Hirooka Sgobin**
