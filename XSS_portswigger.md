# XSS Labs Notes

---

## Lab 1

```html
<svg/onload=alert(1)>
```

**svg** : An HTML tag used to define a Scalable Vector Graphic.  
Because SVGs are written in code, they are part of the DOM (Document Object Model). This means they can contain scripts and interact with the browser just like HTML can.

**onload**: An event attribute that triggers when the element has finished loading.

To check if the server is vulnerable or not we test it out using:

```
<{something}
```

If the `<` is not converted then the field is vulnerable to XSS.

```html
<script>alert(1)</script>
```

---

## Lab 2

Since we see multiple fields, check out with all of them.  
When the page loads again the alert is shown.

---

## Lab 3

```html
"<svg/ onload=alert(1)>
```

When the browser encounters `<svg onload=alert(1)>`, it goes through these steps:

- **Parsing**: The browser reads the HTML and sees a new tag starting.
- **Object Creation**: It creates a "container" for the graphic in the computer's memory.
- **The "OnLoad" Event**: Once the browser finishes setting up that SVG object in the memory, it triggers a "Success!" signal called the `onload` event.
- **Execution**: The browser looks for any instructions attached to that signal. In this case, it finds `alert(1)` and immediately pauses everything else to run that JavaScript command.

In the HTML standard, any attribute starting with `"on"` (like `onload`, `onclick`, or `onerror`) is a reserved "hook" specifically designed to execute JavaScript.

---

## Lab 4

When something is searched inside the searchbox it is placed inside a `<span>` element using an `innerHTML` assignment.

`<span>` is used to define a specific area in HTML, and then use JavaScript's `innerHTML` to change that area.

When a `<script>` is kept inside an `innerHTML` it will not be executed.

The `'` closes the attribute, and the `>` closes the entire tag.

A **tag** defines the structure or type of an element (e.g., paragraph, image, link) using angle brackets, such as `<p>` or `<img>`.

An **attribute** is a modifier added inside an opening tag to provide additional information or change the behavior of that element, usually in `name="value"` pair.

Browsers treat SVG elements as separate documents or namespaces that can have their own events.

```js
payload: <svg/onload=alert(!)
```

---

## Lab 5

To solve this lab, make the "back" link alert `document.cookie`.

Find the back link in the feedback page.

```js
$(function() {
    $('#backLink').attr("href", (new URLSearchParams(window.location.search)).get('returnPath'));
});
```

This is the function made for "back" link.

```js
$(function() { ... });
```

Ensures that the code only runs after the page is fully loaded.

```js
$('#backLink'):
```

This part selects the specific element on the page that has the unique ID `"backLink"`.

`window.location.search`: Looks at the "query string" of the current URL (everything after the `?`).

`new URLSearchParams(...)`: Creates a tool to easily read those parameters.

```js
new URLSearchParams(window.location.search)
```

Instead of you having to write complex code to find where the `returnPath` starts and ends, this will get it for you.

```js
.get('returnPath')
```

This takes the string value `returnPath` contains.

JavaScript can be passed as a part of `href` attribute on an anchor tag.

You can use the `javascript:` URL scheme to execute code directly in the browser's address bar or within an `<a>` tag's `href` attribute.

---

## Lab 6

Note that the vulnerability isn't in an external library, but in the HTML itself.

inline means the script is written in the page , else the code would be take from another file

```js
<scipt></script>
<script src=....></script>
```

it was a hashchange event : This is a JavaScript "sensor" that waits for the part of the URL after the # to change.

The script is programmed to automatically "do something" the moment the URL changes. This is the entry point for the attack.

The script takes that string and injects it into an `<h2>` header or searches for a header based on that value. Because it uses a method like `.innerHTML` or a jQuery selector `$()`, it treats the string as HTML code rather than just text.

the slice part remove the "#" character

location.hash can make an html object

```js
<img src=x onerror=print(1)>
```

this would cause the page to trigger a print case

If you just send someone a link with the payload already at the end (://site.com<img...), the hashchange event won't fire because the hash hasn't "changed" yet—it was just there when the page loaded.

thus we will make an iframe which will first load the page the calls the print function

```js
<iframe src= "https://0a02001c031634a7807c03fe00ed0034.web-security-academy.net/#" onload="this.src+='<img src = x onerror=print()>' ">
</iframe>
```

The iframe loads the original site with a blank hash (`/#`)

this code works since the img source is given as x it is guaranteed to give error which we will use

---

## Lab 7

To solve this lab, perform a cross-site scripting attack that injects an attribute and calls the alert function.

angle brackets are off, thus onload will not be working

we have to find out eventhandlers that does not require the usage of "<>"

so we bruteforce it using the cheat code given in burpsuite

onmouseover seems to work (check render)

```js
```

---

## Lab 8

submit a comment that calls the alert function when the comment author name is clicked.

When the columns given are filled and submitted , the name will be shown as a hyperlink.

"https://0ae6001703a8b22f831affb800d900fd.web-security-academy.net/123.com"

the line we give is appended into the website

```js
javascript:alert(1)
```

javascript: This tells the browser's address bar to treat the following text as code

when the username is clicked the url that will load will be malicious one we gave

---

## Lab 9

To solve this lab, perform a cross-site scripting attack that breaks out of the JavaScript string and calls the alert function.

in js input is usually taken in as var = " input " ;

```js
'; alert(1); //
```

to break out of the original input we use the first ";"

then we call the function alert to carry out the task

we end the function using ";"

to comment out the rest of the unwanted things we use "//"

---

## Lab 10

it was said that the vulnerability layed in the document.write

in the source code when we search for document.write it was seen that the web accepted a parameter named storeId

---

## Lab 11

AngularJS is a structural framework for dynamic web apps

Once the page loads, the AngularJS library "scans" the page, finds double curly braces `{{ }}`, and replaces it with the actual input

AngularJS only works on parts of the page where it is told to

attributes are special words placed inside the opening tag of an element to provide additional information or configure its behaviour.

- src in `<img>`: Specifies the image source.
- href in `<a>`: Specifies a link's destination.

When AngularJS initializes, it scans the DOM for directives. If it finds user-controlled input inside an `ng-app` block, it will treat anything inside double curly braces `{{ }}` as a code expression to be executed.

Thus if given a mathematical eq inside `{{ }}` it is executed

We can not use alert directly inside `{{ }}`

So we create a function which we will call later to execute

This is done using `.constructor`

There are a set of pre methods provided by angularjs , which can be used as an anchor for the `.constructor` method  
(`$scope.methodName`):

`$scope` stores data and functions, not just variable names

In JavaScript, the Function constructor is a built-in way to create new functions dynamically from strings.

```js
{{ $eval.constructor('alert()')() }}
```

The extra `()` is for calling the function

---

## Lab 12

Eval() - it runs the js passed on as a string and returns the completion value

---

## Lab 13

It is evident that in the blog comment we are giving the angle brackets are going to be replaced/encoded

But `.replace` is vulnerable if the pattern it is searching for is a string it will only replace the first occurrence thus if it is repeated it doesn’t mind it

So if we give a mock angle bracket which it is trying to replace and then give the code , we can bypass this

```js
<><img src=x onerror='alert(1)'>
```

---

## Lab 14

use Intruder to brute force and find the tags that are acceptable

do the same and find the events that are accepted

in a html document only one body tag can be used , when more than one body tag is present the browser will remove the second body tag but preserve the element and add to the previous body tag

onratechange works on audio tags or something similar when the playback speed is changed the element runs

An iframe (inline frame) is an HTML element that embeds another HTML document inside the current webpage, essentially creating a "webpage within a webpage".

Represented by the `<iframe>` tag, it is commonly used to embed interactive content like YouTube videos, Google Maps, advertisements, and external widgets without reloading the main page.

```html
<iframe src ="https://0a8c009d03bf48218034030000600013.web-security-academy.net/?search=%22%3E%3Cbody%20onresize=print()%3E" onload=this.style.width='100px'>
```

---

## Lab 15

Lab: Reflected XSS into HTML context with all tags blocked except custom ones

When `<script>` is run it shows that the tag isn’t allowed

This is because the page is blocking the known tags here

It was concluded that if we use a tag that is not present in the known list we can bypass this

Custom Tags are a way for developers to create their own reusable HTML tags with unique functionality

For custom tags to be working you need atleast one hyphen

The payload

```js
<custom-tag=1 onclick="alert(1)">
```

Worked

Not all events will be available for custom tags (the usual onload will not work since it is not compatible)

We have to make a payload that once delivered to a victim does the job without the victim having to do anything

So we use the payload

```js
<custom-tag id=x onfocus=alert(document.cookie) tabindex=1 autofocus>
```

On focus is a much preferred method

onfocus: Triggers when the tag is focused (requires tabindex and either autofocus or a URL hash).

Tag tells the browser how to work with the given content

But using a custom-tag the browser treats this as an empty container

By default, you cannot "focus" on a custom tag. If you click it or try to tab to it, nothing happens.

This is where tabindex helps , it tells the browser that even though this is a custom-tag use it as a button and let it to be part of the interactive elements on the page.

The autofocus makes the content focus as soon as the page finishes loading

### Custom-tags:

When a browser sees `<abc>` it treats an HTMLUnknownElement

But attributes like `onclick` , `onmouseover` are still interpreted as JS

---

## Lab 16

This lab has a simple reflected XSS vulnerability. The site is blocking common tags but misses some SVG tags and events.

We use burp to find the different types of events that are allowed in this

<img width="940" height="174" alt="image" src="https://github.com/user-attachments/assets/10f68b9d-32fa-4d41-8248-119c9a409247" />


Except for the ones using the <svg>, <animateTransform> rest got 400 response 

The <animateTransform> SVG element animates a transformation attribute on its target element, thereby allowing animations to control translation, scaling, rotation

### svg:
An XML-based markup language for describing two-dimensional based vector graphics.

SVG provides elements for circles, rectangles, and simple and complex curves

these elements are the basic "building blocks" used to create images. Since SVG is an XML-based format, you define these shapes using specific tags and attributes that tell the browser exactly where and how to draw them.

animateTransform as a nested element. it doesnt work if its given  as an attribute of the <svg> tag.
