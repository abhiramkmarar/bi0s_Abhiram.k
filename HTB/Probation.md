# CVE-2021-41773 — Apache 2.4.49 Path Traversal & RCE

---

## 1. Introduction

CVE-2021-41773 is a vulnerability in Apache HTTP Server version 2.4.49.

It is primarily a path traversal vulnerability, which can lead to Remote Code Execution (RCE).

---

## 2. What is Apache HTTP Server?

Apache is a widely used web server that:
- Serves web pages (HTML, CSS, JS)
- Handles HTTP requests
- Can execute backend scripts (via modules like CGI)

---

## 3. What is Path Traversal?

Path traversal is a vulnerability where an attacker can:
- Access files outside the intended directory
- Escape the web root (e.g., /var/www/html)

Example:
../../etc/passwd

---

## 4. Root Cause of CVE-2021-41773

The vulnerability exists due to improper path normalization.

---

## 5. What is Path Normalization?

Before processing a request, the server should:
1. Decode encoded characters (%2e - .)
2. Simplify paths (../)
3. Apply access control

---

## 6. What went wrong?

Apache:
- Did NOT properly normalize encoded paths
- Applied access control before full normalization

Example:
.%2e/ - ../

---

## 7. Impact

File Disclosure:
- Read sensitive files like /etc/passwd

Remote Code Execution (RCE):
- Execute commands 

---

## 8. What is CGI?

CGI (Common Gateway Interface) allows a web server to:
- Execute programs/scripts
- Return their output to the user

Example:
Request - /cgi-bin/script.sh  
Server executes script and returns output

---

## 9. What is /bin/sh?

/bin/sh is:
- A system shell (command interpreter)
- Used to execute commands on Linux

Example:
id - prints user identity

---

## 10. Lab Setup

Command:
docker run -d --name vuln-apache -p 8080:80 httpd:2.4.49

Explanation:
- docker run - start container
- -d - run in background
- --name - container name
- -p 8080:80 - map port
- httpd:2.4.49 - vulnerable version

Result:
Server runs at http://localhost:8080

---

## 11. Configuration Change

```bash
docker exec vuln-apache sed -i 's/Require all denied/Require all granted/g' /usr/local/apache2/conf/httpd.conf && docker restart vuln-apache
```
Explanation:
- docker exec - run command inside container
- sed - edit file
- Require all denied - blocks access
- Require all granted - allows access
- restart - apply changes

Reason:
To allow traversal to reach system files

---

## 12. Exploitation

```bash
curl -s "http://localhost:8080/cgi-bin/.%2e/.%2e/.%2e/.%2e/etc/passwd"
```
Explanation:
- curl - send HTTP request
- -s - silent
- .%2e/ - encoded ../

---

## 13. What Happened?

1. Request sent with encoded traversal
2. Apache failed to normalize path
3. Security check bypassed
4. Accessed /etc/passwd

---

## 14. What is /etc/passwd?

- System file containing user account info
- Includes usernames and IDs

---

## 15. Transition to RCE

Instead of:
Reading /etc/passwd

Target:
/bin/sh

---

## 16. How RCE Works

1. Use traversal to reach /bin/sh
2. Send command in request
3. Apache treats it as CGI script
4. Command executes

---

## 17. Why CGI matters

Without CGI:
- Files are read only

With CGI:
- Files are executed

---

## 18. Conditions for RCE

RCE requires:
- Path traversal working
- CGI enabled
- Executable accessible

---


<img width="947" height="515" alt="image" src="https://github.com/user-attachments/assets/37ebc71d-05e5-42df-9f23-2141f8b838f4" />

## 19. Why This Leads to Command Execution

### Traversal Bypass
The request uses encoded traversal sequences (like `.%2e/`) to escape the web root.  
Due to improper path normalization in Apache 2.4.49, these bypass access control checks.

### Reaching an Executable
The traversal resolves to `/bin/sh`, which is the system shell (an executable program used to run commands).

### CGI Handling
The `/cgi-bin/` path is configured for CGI (**Common Gateway Interface**).  
CGI tells Apache to treat the target as a program and execute it instead of serving it as a file.

### Command Injection via Request Body
The shell interprets input like `echo; id` as commands and executes them.

### Response Returned
The output of the executed command is sent back to the client as part of the HTTP response.


## 20. Security Issue

Main issue:
Validation done before normalization

Correct approach:
Normalize first, then validate

---

## 21. Fix

Upgrade Apache:
- 2.4.50 (partial fix)
- 2.4.51 (complete fix)

---

## 22. Configuration Fixes

- Disable CGI if not needed
- Restrict directory access
- Use proper permissions

---

## 23. Summary

CVE-2021-41773:
- Improper normalization vulnerability
- Allows encoded traversal
- Leads to file disclosure
- Can lead to RCE if CGI is enabled

---
