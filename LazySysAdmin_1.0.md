# LazySysAdmin – VulnHub Walkthrough

Target: LazySysAdmin (VulnHub VM)  
Attacker Machine: Ubuntu/Kali  
Platform: VirtualBox (Host-Only Network)
**Vulnerability: Information Leakage via SMB and Password Reuse.**
---

# 1. Network Discovery

Since the machine is running in a **Host-Only virtual network**, the first step is identifying the IP address of the target machine.

Typical tools used:

```bash
netdiscover
```

or

```bash
arp-scan -l
```

After discovering the target IP, we proceed to **port scanning and enumeration**.

---

# 2. Port Scanning

To discover open services and possible attack vectors we run:

```bash
nmap -A -p- <TARGET-IP>
```

Explanation:

- **-A** > Enables OS detection, version detection, script scanning
- **-p-** > Scans all TCP ports

### Scan Results

<img width="1330" height="690" alt="image" src="https://github.com/user-attachments/assets/12b3ff8b-6739-4f47-b012-633eb3072a27" />

<img width="1330" height="690" alt="image" src="https://github.com/user-attachments/assets/52b15fb0-a740-440b-b0f8-5ee8f43bedb3" />

Important open ports discovered:

```
22  > SSH
80  > HTTP
139 > SMB (NetBIOS Session Service)
445 > SMB (Direct SMB over TCP)
```

---

# 3. SMB Enumeration

Ports **139 and 445** indicate that **Samba / SMB services** are running.

### What is SMB?

**SMB (Server Message Block)** is an application-layer network protocol used for:

- File sharing
- Printer sharing
- Network resource access

smb hosts the content to be shared in a server to acces it you'll connect to it and download it

### NetBIOS

**NetBIOS (Network Basic Input/Output System)** is a legacy API that allows applications on different computers in a **Local Area Network (LAN)** to communicate.

Port usage:

```
139 > SMB over NetBIOS
445 > Direct SMB over TCP
```

---

# 4. Listing SMB Shares

We can list available shared folders using **smbclient**.

```bash
smbclient -L //192.168.56.104 -N
```

Explanation:

- **smbclient** > Tool from the Samba suite to interact with SMB shares
- **-L** -> List available shares
- **//192.168.56.104** > Target system
- **-N** -> Anonymous login (no password)

<img width="1330" height="690" alt="image" src="https://github.com/user-attachments/assets/3906b475-6c9d-489e-a9a1-932202fdee70" />

---

### SMB Naming Convention

In SMB share names:

```
$  = Hidden share
```

These shares normally do not appear in Windows file explorer but can still be accessed if permissions allow.

---

# 5. Accessing the Share

After identifying accessible shares, we connect to one.

Example:

```bash
smbclient //192.168.56.104/share_name -N
```

Once connected we can run SMB commands like:

```
ls      > list files
cd      -> change directory
get     > download file
put     -> upload file
```

Inside the share we discovered several files.

<img width="975" height="413" alt="image" src="https://github.com/user-attachments/assets/7fc7aa62-d544-427c-89c7-ff0942740a68" />

<img width="975" height="413" alt="image" src="https://github.com/user-attachments/assets/4e91a206-1d0e-44a4-a565-0d7d8f2c6489" />

---

# 6. Discovering Sensitive Files

During enumeration we discovered a **config.php** file.

<img width="1441" height="700" alt="image" src="https://github.com/user-attachments/assets/f2f07d43-e087-47d5-8428-b0d9c6cfd692" />

Configuration files often store:

- Database credentials
- System usernames
- Passwords
- API keys

---

# 7. Extracting Credentials

Inside `config.php` we found credentials.

<img width="1441" height="700" alt="image" src="https://github.com/user-attachments/assets/45ad85fd-4740-49a5-9b02-310475f0ed30" />

These credentials can potentially be reused for:

- Web login
- SSH login
- Database login

---

# 8. Further Enumeration

After retrieving credentials, we continue exploring the system.

<img width="1508" height="629" alt="image" src="https://github.com/user-attachments/assets/febcd07f-cd00-4e9f-b4e8-91649230c158" />

<img width="1123" height="198" alt="image" src="https://github.com/user-attachments/assets/76d32b0c-6369-49de-b4be-c376fa73a2fa" />

---

# 9. Solving

While exploring the web server, additional files and directories were found.

<img width="614" height="776" alt="image" src="https://github.com/user-attachments/assets/cd56600a-93fb-4a02-9a65-1fcd13361fd7" />


---

# 10. Key Takeaways


### SMB Enumeration

Anonymous SMB access can expose sensitive files if shares are misconfigured.

<img width="851" height="241" alt="image" src="https://github.com/user-attachments/assets/6881a698-2e9b-4abf-8c4f-bee98f5debbc" />

### Credential Reuse

Credentials discovered in one service can frequently be reused in another service (SSH, web admin, etc.).
