# SickOs 

Target: SickOs (VulnHub VM)  
Platform: VirtualBox (Host-Only Networking)  

**Vulnerability: file upload **
---

## Network

The virtual machines were configured using **Host-Only Adapter** in VirtualBox.

In Host-Only mode:

- The attacker (ubuntu) and target (SickOs) exist in a private virtual network.
- They can communicate with each other.
- They are isolated from the external internet.

---

## Understanding Subnet & Subnet Mask

When running:

```bash
ip a
```

it showed:

```bash
192.168.56.102/24
```

/24 is CIDR notation representing the subnet mask.

This means:

- First 24 bits = Network portion  
- Last 8 bits = Host portion  

192.168.56.0 – 192.168.56.255 ports are available

```
192.168.56.0   : Network address
192.168.56.255 : Broadcast address
Usable hosts   : 192.168.56.1 – 192.168.56.254
```

<img width="1311" height="738" alt="image" src="https://github.com/user-attachments/assets/0ad1966c-b73e-428e-a0d0-1f3bd2a30950" />

---

## ARP

ARP = Address Resolution Protocol  

It lists all known mappings of IP addresses to physical MAC addresses for devices on the local network,  
helping troubleshoot network connectivity issues and identify devices on the subnet.  

Host seems down, since it might be a firewall or it's using a proxy to gain access  

the **-Pn** tells Nmap to treat it as if the host is up  

```
Ports discovered:

22/tcp open ssh
3128/tcp open http-proxy (Squid )

Filtered Ports:
65533 TCP ports filtered (no response)
```

ssh suggests a linux cmd  

squid is a proxy  

---

## Proxy

A proxy server acts as an intermediary between:

Client → Proxy → Target Server  

Instead of the client communicating directly with the web server, requests go through the proxy.

---

using FoxyProxy we set proxy  

<img width="1287" height="676" alt="image" src="https://github.com/user-attachments/assets/6ce3ae86-a2cc-436d-b4ec-cb06e4d924ba" />

now we enter the website 192.168.56.130  

and check the usual `/robots.txt`  

<img width="685" height="109" alt="image" src="https://github.com/user-attachments/assets/89e003af-e33a-47a9-88bf-a5121bcbe963" />

we enter `/wolfcms`  
it is a site content managing system  

<img width="846" height="399" alt="image" src="https://github.com/user-attachments/assets/89b1f97d-cc6f-46fa-a88d-642b9d85c782" />

now we search for the vulnerabilites wolfcms might contain 
<img width="1068" height="690" alt="image" src="https://github.com/user-attachments/assets/d8dea932-e34c-41c0-a60b-28ab8122650c" />

and we got hit on admin/login  

<img width="841" height="310" alt="image" src="https://github.com/user-attachments/assets/73bc1214-f924-4111-b71c-9c1c9824b2ed" />

in google it was said that default credentials were

```
admin:admin
```

<img width="847" height="391" alt="image" src="https://github.com/user-attachments/assets/796ad7ae-2f64-4472-807f-3edce3dbe2c2" />

---

## Code Execution via PHP

now we check around the website  

the home page code had php running in it  

<img width="1519" height="735" alt="image" src="https://github.com/user-attachments/assets/52bc1ba8-f023-4cc8-89c0-ddd18ea4db0a" />

so we try to show the file in the page using the php code  

```php
<?php
$path = '/'; 
$files = scandir($path);

echo "<h3>Contents of $path :</h3><pre>";
foreach($files as $file) {
    echo $file . "\n";
}
echo "</pre>";
?>
```

The `scandir()` function reads everything inside the specified `$path` (files and folders) and stores them as an array in the variable `$files`.

```
echo "<h3>Contents of $path :</h3><pre>";
```

This shows the path being scanned.  
The `<pre>` (preformatted text) tag is used so that any line breaks or spaces in the output are displayed exactly as they appear in the code.

```
foreach($files as $file) { ... }
```

This loop goes through the `$files` array one item at a time.  
In each "turn" of the loop, the current filename is assigned to the variable `$file`.

```
echo $file . "\n";
```

This prints the name of each file or folder followed by a new line character (`\n`).

<img width="1519" height="735" alt="image" src="https://github.com/user-attachments/assets/1f6e861b-4bd6-4197-aa8c-100a74e66e42" />

now we go in and find the password in the page  

```
/var/www/wolfcms/config.php
```

we get the credentials as  

```
sickos:john@123
```

---

## Privilege Escalation

now we simply login in the target machine  

```bash
sudo su
```

this gives you root access  

now we find the flag  

<img width="848" height="631" alt="image" src="https://github.com/user-attachments/assets/22e4bdff-bf44-49aa-8dea-28eb0b8d5781" />

---
