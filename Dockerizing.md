# Dockerizing 
##  Project Overview

Consisting of:

* A PHP-based web 
* A MySQL database 



## Work-Flow

```
User Browser
     |
     
Host Machine (Port 8080)
     |
     

 PHP Web Container     
 (user-system-web)     
 Port: 80              

            │ Docker Network

 MySQL Container       
 (user-system-db)                

            │
        Docker Volume
```

---


* Docker
* Docker Compose
* PHP 8 (Apache)
* MySQL 8.0
* Linux (Ubuntu)

---

## Floder Structure

```
user-system-docker/
 docker-compose.yml
 Dockerfile
 src/
    index.php
    login.php
    register.php
    db.php
    uploads/

```

---

##  Database 

 `users` table structure:

| Column               | Type    | Description         |
| -------------------- | ------- | ------------------- |
| id                   | INT     | User ID             |
| username             | VARCHAR | Username            |
| email                | VARCHAR | Email address       |
| password             | VARCHAR | MD5 hashed password |
| role                 | VARCHAR | user / admin        |
| profile_picture_path | VARCHAR | Upload path         |

---

##  Docker Components Explained

### 1️ Docker Image

A **Docker image** is a read-only blueprint used to create containers.

Images used:

* `user-system-web`
* `mysql:8.0`
---

### 2️ Docker Container

A **container** is a running instance of an image.

Running containers:

* `user-system-web`
* `user-system-db`

---

### 3️ Docker Network

A network allowing containers to communicate using service names.



---

### 4️ Docker Volume

Used to keep MySQL data so it is not lost when containers stop or restart.

---

### 5️ Docker Compose

Docker Compose is used to manage multi-container applications with a single command.

Command:

```
docker-compose up -d
```

---

## How to Run 

### Prerequisites

* Docker installed
* Docker Compose installed

### Steps

```

# Start containers
docker-compose up -d

# Access the website
http://localhost:8080
```

---

##  Security & Pentesting Relevance

This setup is useful for:

* Creating isolated testing environment
* Running vulnerable applications safely
* Practicing web and database security testing
* Reproducing bugs and exploits reliably

Common pentesting use cases:

* SQL Injection testing
* Authentication bypass testing
* File upload vulnerabilities

---

##  VM vs Docker Container

| Virtual Machine     |  Docker Container   |
|---------------------- --------------------|
| Heavy                | Lightweight        |
| Own OS               | Shares host kernel |
| Slow startup         | Quick startup      |
| High resource usage  | Efficient          |

---
## codes

| Action          | With Docker            |
| --------------- | ---------------------- |
| Start website   | `docker-compose up -d` |
| Start database  | `docker-compose up -d` |
| Stop website/db | `docker-compose down`  |
| Check status    | `docker ps`            |
                   

---
## Points

* Docker packages applications and dependencies together
* Containers are isolated but lightweight
* Volumes ensure data persistence
* Ideal for development, deployment, and security testing

---


