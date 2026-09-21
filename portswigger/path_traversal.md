# Path Traversal Labs

## Lab 1
### File path traversal, simple case
# Objective:
          To retrieve the contents of the '/etc/passwd' file by exploiting a path traversal vulnerability in the product image display feature
# Steps:
     > Opened the product page and noted that the image is loaded via a URL containing a 'filename' parameter, for example '?filename=example.jpg'
     > Intercepted the image request in Burp Suite (or edited the URL directly in the browser)
     > Changed the 'filename' parameter value to '../../../etc/passwd'
     > Here,
           * '../' means move up one directory
           * repeating it several times walks back up to the filesystem root before descending into '/etc/passwd'
           * extra '../' beyond the root is harmless, since Linux just stays at '/'
     > Forwarded the modified request
     > The response returned the contents of '/etc/passwd' instead of an image
# Result: Lab solved — contents of /etc/passwd retrieved
# Root cause: user input is concatenated directly into a filesystem path with no sanitisation or restriction to an intended directory

## Lab 2
### File path traversal, traversal sequences blocked with absolute path bypass
# Objective:
          To retrieve '/etc/passwd' when the application blocks traversal sequences like '../'
# Steps:
     > Intercepted the image request in Burp Suite
     > Tried '../../../etc/passwd' first — application stripped or rejected the traversal sequence, confirming some filtering was in place
     > Since the application blocks relative traversal but doesn't validate that the path stays inside the images directory, supplied an absolute path instead
     > Changed the 'filename' parameter value to '/etc/passwd'
     > Here, the application takes the parameter and appends or uses it directly as a filesystem path, so a full absolute path overrides the intended base directory entirely
     > Forwarded the modified request
     > The response returned the contents of '/etc/passwd'
# Result: Lab solved — filter bypassed using an absolute path
# Root cause: the filter only checks for traversal sequences (e.g. '../') and does not confirm the final resolved path is actually inside the permitted directory

## Lab 3
### File path traversal, traversal sequences stripped non-recursively
# Objective:
          To retrieve '/etc/passwd' when the application strips '../' sequences, but only once per occurrence
# Steps:
     > Intercepted the image request in Burp Suite
     > Tried '../../../etc/passwd' — the traversal sequences were stripped out, leaving something like 'etc/passwd', so it failed
     > Since the filter removes '../' but does not run repeatedly until no sequences remain, nesting an extra set of characters around each sequence leaves a valid one behind after a single pass of stripping
     > Changed the 'filename' parameter value to '....//....//....//etc/passwd'
     > Here,
           * the filter finds '../' inside '....//' and removes it
           * what's left after removal is '../' again, since '....//' minus '../' leaves '../'
           * because the filter isn't applied recursively, the surviving '../' sequences are not caught a second time
     > Forwarded the modified request
     > The response returned the contents of '/etc/passwd'
# Result: Lab solved — filter bypassed using nested traversal sequences
# Root cause: the sanitisation logic performs a single, non-recursive removal pass, so overlapping or nested sequences reconstruct a valid traversal string after filtering

## Lab 4
### File path traversal, traversal sequences stripped with superfluous URL-decode
# Objective:
          To retrieve '/etc/passwd' when the application URL-decodes the input twice, allowing a double-encoded payload to slip past the filter
# Steps:
     > Intercepted the image request in Burp Suite
     > Tried '../../../etc/passwd' and standard single URL-encoding ('..%2f..%2f..%2fetc/passwd') — both were detected and stripped/blocked
     > Since the application decodes the input a second time after the traversal filter has already run, an input that is safe-looking at filter-time but decodes into a traversal sequence afterward will bypass the check
     > Changed the 'filename' parameter value to '..%252f..%252f..%252fetc/passwd'
     > Here,
           * '%25' is the URL-encoded form of the '%' character
           * so '%252f' first decodes to '%2f', which still isn't a raw '../' at the time the filter checks it
           * the filter sees '%2f' (not a literal traversal sequence) and lets it through
           * the application then performs a second decode, turning '%2f' into '/', which reconstructs '../' after validation has already passed
     > Forwarded the modified request
     > The response returned the contents of '/etc/passwd'
# Result: Lab solved — filter bypassed using double URL-encoding
# Root cause: input is decoded twice while the traversal check runs only once (before the second decode), so an encoded payload that looks harmless at filter-time becomes a traversal sequence after the redundant decode

## Lab 5
### File path traversal, validation of start of path
# Objective:
          To retrieve '/etc/passwd' when the application only checks that the supplied path starts with the expected base directory
# Steps:
     > Intercepted the image request in Burp Suite
     > Noted the application requires the filename to begin with '/var/www/images/', likely checked with something like 'startswith()'
     > Since the check only validates the start of the string and doesn't resolve the path afterward, appending traversal sequences after a valid-looking prefix still satisfies the check while escaping the directory once the OS resolves the full path
     > Changed the 'filename' parameter value to '/var/www/images/../../../etc/passwd'
     > Here,
           * the string still starts with '/var/www/images/', so the validation passes
           * once the OS resolves the full path, the '../../../' sequences walk back up past the images directory and out to the root before descending into '/etc/passwd'
     > Forwarded the modified request
     > The response returned the contents of '/etc/passwd'
# Result: Lab solved — filter bypassed by satisfying the required prefix and then traversing out of it
# Root cause: the validation only checks the beginning of the path string; it doesn't canonicalise the path first, so traversal sequences later in the string are never accounted for

## Lab 6
### File path traversal, validation of file extension with null byte bypass
# Objective:
          To retrieve '/etc/passwd' when the application checks that the supplied filename ends in an allowed image extension (e.g. '.png')
# Steps:
     > Intercepted the image request in Burp Suite
     > Tried a plain traversal payload ('../../../etc/passwd') — rejected, since it doesn't end in an approved image extension
     > Since the extension check happens on the raw string but the underlying file-opening function historically treats a null byte as a string terminator, appending an approved extension after an encoded null byte satisfies the check while the OS/filesystem call reads only the string up to the null byte
     > Changed the 'filename' parameter value to '../../../etc/passwd%00.png'
     > Here,
           * '%00' is the URL-encoded null byte
           * the extension check sees the string ending in '.png' and passes it
           * when the underlying file-read call processes the string, it stops at the null byte, so it actually opens '../../../etc/passwd' and ignores '.png' entirely
     > Forwarded the modified request
     > The response returned the contents of '/etc/passwd'
# Result: Lab solved — filter bypassed using a null byte to truncate the string after validation
# Root cause: the extension check validates the full string, but the file-handling code (in older/unpatched language runtimes) treats '%00' as end-of-string, creating a mismatch between what's validated and what's actually opened

---

**Note:** these follow PortSwigger's documented lab titles and techniques (not independently re-verified live here). In a review, press on Lab 3 and Lab 6 especially — a candidate who only memorized the payload string will struggle to explain the non-recursive stripping or null-byte truncation behavior in their own words.
