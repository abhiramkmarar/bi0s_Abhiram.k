# Target Machine : RICKdiculouslyEasy

---

## Network Setup

**Network Type:** Host-Only Adapter(set by us) so that we can connet to it sung another machine created an isolated network  

since we dont know what the ip adress is, we have to find it  

```bash
ip a
```

IPADRESS : 192.168.56.102  

now we have to know which all ports are open and avalable  

```bash
nmap -A -p- 192.168.56.101
```

the -sV flag checks and tell us the versions and -sC flag tels us about the possible vulnerabilites that could be present in the  

<img width="1172" height="763" alt="image" src="https://github.com/user-attachments/assets/a27051f2-d4ff-4a09-8eaf-c63a269cef98" />
<img width="1177" height="776" alt="image" src="https://github.com/user-attachments/assets/e8aa215d-c906-4e20-9e6c-d9da468ca4fa" />

-A  compiles -sC and -sV  

The scan revealed:

```
21/tcp  ftp vsftpd 3.0.3
22/tcp  ssh
80/tcp  http 
9090/tcp  http zeus-admin
13337/tcp  unknown
22222/tcp  ssh  7.5
60000/tcp  unknown
```

we'll start checking into each now  

---

## ftp 

FTP (File Transfer Protocol) is used for transferring files between systems.  
Default port: 21  

```bash
ftp <IP>
```

searched for common usernames and password  

```
anonymous - [blank]
```

now it using ls we can find the password file and then we'll extract it using get  

```
1: FLAG{Whoa this is unexpected} 
```

---

## http

Nikto is a web vulnerability scanner  

- Detect misconfigurations  
- Find dangerous files  
- Identify hidden directories  

By default, Nikto scans port 80. If your target uses a different port use -h with the -p (port) flag  

```bash
nikto -h http://192.168.56.101
```

<img width="909" height="507" alt="image" src="https://github.com/user-attachments/assets/9f131a66-9fb0-43c1-9372-2dbf0b1a86fa" />

nikto found that  

/passwords/ directory indexing enabled  
robots.txt exists  

<img width="651" height="296" alt="image" src="https://github.com/user-attachments/assets/f83da5d6-c77c-4c55-b6da-4d33c8351c7d" />

found a flag in the FLAG.txt file.

```
FLAG{Yeah d- just don’t do it.} 
```

also found a password called "winter" in the page source of the passwords.html file, which would be used later on  

robots.txt is used to instruct search engines which directories to avoid indexing  
Administrators often use the Disallow directive to keep search engines away from sensitive directories like /admin/, /backup/, or /config/  
By listing these "private" paths publicly, the file unintentionally reveals the site's internal structure to anyone who looks at it  

<img width="667" height="315" alt="image" src="https://github.com/user-attachments/assets/243a43a6-2433-4603-ae1a-a60f46aae614" />

cgi is a standard way for a web server (like Apache) to execute external programs and return their output as a web page.  

<img width="602" height="623" alt="image" src="https://github.com/user-attachments/assets/fc883456-a5bc-4772-afc7-79a0247601a8" />

we try to use another command out of the intended trace using ";" as in bash  
from here on ward we'll use thia as a command line  

in the /etc folder there was a file nammed passwd  

<img width="547" height="117" alt="image" src="https://github.com/user-attachments/assets/ed7f620a-93fe-4dea-9fca-1e1559422d70" />

the /etc/passwd file is a plain-text database that stores essential information for every user account on the system  

so we find the logs , essentialy this gives us the users names  
(we had to use tail since the latest users would be at the very bottom)  

<img width="562" height="389" alt="image" src="https://github.com/user-attachments/assets/679590b2-1326-4355-a4c9-faac55adb5a2" />

Found users:

```
RickSanchez
Morty
Summer
```

---

## ssh

now that we have 3 usernames and a password we'll try ssh  

ssh is a network protocol that gives you a secure way to access a computer over an unsecured network  

since the password is winter we try username Summer  

```bash
ssh Summer@192.168.56.105 
```

this failed so we try with other port we know 22222  

```bash
ssh Summer@192.168.56.105 -p 22222
```

<img width="671" height="296" alt="image" src="https://github.com/user-attachments/assets/0a2ce172-ceab-4331-9bf7-4adeef642158" />

now that we have terminal we explore  

```
FLAG{Get off the high road Summer!}
```

<img width="671" height="296" alt="image" src="https://github.com/user-attachments/assets/c10fd79d-1581-4fea-b3b0-03711974b397" />

since we cant extract the safe_password from here we copy to our own system using the command  

```bash
scp -P 22222 Summer@192.168.56.101:/home/Morty/Safe_Password.jpg .
```

scp: The command used to copy files securely over an SSH connection  

now we get the password and extract the flag  

```
password: Meeseek
```

<img width="1512" height="428" alt="image" src="https://github.com/user-attachments/assets/09085dc4-ab30-4913-9fe4-e77913efd915" />

```bash
FLAG: {131333}
```

now we check the last directory of RickSanchez  

<img width="839" height="230" alt="image" src="https://github.com/user-attachments/assets/17fb78c3-2777-4478-a005-2fb4406d9de6" />

we ectract the 'safe' to our system  

<img width="1512" height="85" alt="image" src="https://github.com/user-attachments/assets/1bd3c83d-a278-43a4-a6c8-ced3bb44deb8" />

since it was an excecutable format we run it using  

```bash
./safe
```

it says to use cmd line arg  

so use give it again using the previous flag as a command line argument  

these are values you pass to a programm when you run it  

```bash
echo hi
```

hi is an argument  

```
FLAG{And Awwwaaaaayyyy we Go!}
```
<img width="1386" height="480" alt="image" src="https://github.com/user-attachments/assets/5779327f-964a-4765-a5f3-a11c58d18382" />

we see that there is a clue for ricks password 

### In the Rick and Morty episode "Get Schwifty" (Season 2, Episode 5), it is revealed that a young Rick Sanchez formed a band called The Flesh Curtains  

now we make make a wordlist to brute force it 
```bash
crunch 7 7 -t ,%Flesh -o wordlist.txt

crunch 10 10 -t ,%Curtains >> wordlist.txt
```
<img width="878" height="382" alt="image" src="https://github.com/user-attachments/assets/3626b84e-b00b-40d2-9bd4-6f61ea5e3ff8" />

```bash
hydra -l RickSanchez -P wordlist.txt ssh://192.168.56.101 -s 22222
```
<!--Lowercase L Single username  -->
<!--Uppercase P specifies the wordlist of passwords  -->
<!-- -t 4 could be used if you want to try 4 passwords at the same time -->

now we play around the terminal to find the passswrd in the root 
<img width="1541" height="715" alt="image" src="https://github.com/user-attachments/assets/906428d8-898d-4b0b-8968-a2bbb93b466d" />

```
 FLAG: {Ionic Defibrillator}
```
---

## 9090

checked port 9090 and found a flag on the web page.  

<img width="1388" height="758" alt="image" src="https://github.com/user-attachments/assets/3670413d-cc7d-447b-af2f-9cfcbbc0c1de" />

```
FLAG {There is no Zeus, in your face!}
```

## 13337
<img width="729" height="129" alt="image" src="https://github.com/user-attachments/assets/bbce58d1-fb97-4391-a41f-0bfbe373b66b" />

```
FLAG:{TheyFoundMyBackDoorMorty}
```
<!--  nc : It  can  open  TCP  connections, send UDP packets, listen on arbitrary TCP and UDP ports, do port scanning -->

## 60000

<img width="694" height="185" alt="image" src="https://github.com/user-attachments/assets/5bb728a8-427c-4a0f-873b-97d271e8783a" />

if we notice it shows "#" insted of the usual "$" this means that we have root accsess 
```
FLAG{Flip the pickle Morty!}
```
