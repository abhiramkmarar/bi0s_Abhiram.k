# XSS

---

## Lab 1

- `<svg/onload=alert(1)>`

- *svg : An HTML tag used to define a Scalable Vector Graphic.  
- Because SVGs are written in code, they are part of the DOM (Document Object Model).  
- This means they can contain scripts and interact with the browser just like HTML can  

- *onload: An event attribute that triggers when the element has finished loading.  
- *to check if the server is vulnerable or not we test it out using " <{something}  
- *if the < is not converted then the field is vulnerable to XSS  

- `<script>alert(1)</script>`

---

## Lab 2

- since we see multiple fields check out with all of them  
- when the page loads again the alert is shown  

---

## Lab 3

- `"<svg/ onload=alert(1)>`

- When the browser encounters <svg onload=alert(1)>, it goes through these steps:  
  - Parsing: The browser reads the HTML and sees a new tag starting.  
  - Object Creation: It creates a "container" for the graphic in the computer's memory.  
  - The "OnLoad" Event: Once the browser finishes setting up that SVG object in the memory, it triggers a "Success!" signal called the onload event.  
  - Execution: The browser looks for any instructions attached to that signal. In this case, it finds alert(1) and immediately pauses everything else to run that JavaScript command.  

- In the HTML standard, any attribute starting with "on" (like onload, onclick, or onerror) is a reserved "hook" specifically designed to execute JavaScript  

---

## Lab 4

- use Intruder to brute force and find the tags that are acceptable  
- do the same and find the events that are accepted  
- in a html document only one body tag can be used ,when more than one body tag is present the brower will remove the second body tag but preserve the  
- element and add to the previous body tag  
- onratechange works on audio tags or something similar when the playback speed is changed the element runs  

- An iframe (inline frame) is an HTML element that embeds another HTML document inside the current webpage, essentially creating a "webpage within a webpage".  
- Represented by the `<iframe>` tag, it is commonly used to embed interactive content like YouTube videos, Google Maps, advertisements, and external widgets without reloading the main page.

```html
<iframe src ="https://0a8c009d03bf48218034030000600013.web-security-academy.net/?search=%22%3E%3Cbody%20onresize=print()%3E" onload=this.style.width='100px'>
```
