<img width="1330" height="690" alt="image" src="https://github.com/user-attachments/assets/12b3ff8b-6739-4f47-b012-633eb3072a27" />
<img width="1330" height="690" alt="image" src="https://github.com/user-attachments/assets/52b15fb0-a740-440b-b0f8-5ee8f43bedb3" />
Ports 139 and 445 (Samba/SMB): These ports are used for file and printer sharing via the Server Message Block (SMB) protocol.
Port 139 handles SMB over the legacy NetBIOS session service.

Port 445 is used for modern, Direct Host SMB over TCP/IP.


CTF Tip: On this specific machine, you can often use smbclient to access shared directories anonymously to find sensitive files like deets.txt.


SMB (Server Message Block) is an application-layer network protocol used primarily for providing shared access to files, printers.


NetBIOS (Network Basic Input/Output System) is a legacy session-layer API (Application Programming Interface) that allows applications
on separate computers to communicate over a Local Area Network (LAN)


The client requests access to a specific shared resource (a "tree") on the server
```bash
smbclient -L //SERVER-IP -U username
```
<img width="1330" height="690" alt="image" src="https://github.com/user-attachments/assets/3906b475-6c9d-489e-a9a1-932202fdee70" />

smbclient is a tool from the Samba suite that lets Linux interact with Windows-style SMB file shares.

-L → List available shares

//192.168.56.104 → target machine

-N → no password / anonymous login

In SMB naming conventions:

$ means hidden share.

<img width="975" height="413" alt="image" src="https://github.com/user-attachments/assets/7fc7aa62-d544-427c-89c7-ff0942740a68" />
<img width="975" height="413" alt="image" src="https://github.com/user-attachments/assets/4e91a206-1d0e-44a4-a565-0d7d8f2c6489" />

<img width="1441" height="700" alt="image" src="https://github.com/user-attachments/assets/f2f07d43-e087-47d5-8428-b0d9c6cfd692" />

inside config.php we get 

<img width="1441" height="700" alt="image" src="https://github.com/user-attachments/assets/45ad85fd-4740-49a5-9b02-310475f0ed30" />
<img width="1508" height="629" alt="image" src="https://github.com/user-attachments/assets/febcd07f-cd00-4e9f-b4e8-91649230c158" />
<img width="1123" height="198" alt="image" src="https://github.com/user-attachments/assets/76d32b0c-6369-49de-b4be-c376fa73a2fa" />
<img width="614" height="776" alt="image" src="https://github.com/user-attachments/assets/cd56600a-93fb-4a02-9a65-1fcd13361fd7" />
