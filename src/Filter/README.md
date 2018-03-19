# Input Filters
 
This rules are to be understood according to [RFC2119](https://www.ietf.org/rfc/rfc2119.txt).
 
Input filters check and sanitize untrusted input data which enters the system.
Any input data based on PHP types boolean, integer, strings and (nested) arrays can
be handled.
 
1. Before using any untrusted input from incoming interfaces (user interface, 
   request data, xml imports, soap, rest or similar sources) an input filer
   MUST be used to ensure transformation into a trusted format.
2. The filter MUST be used to sanitize or cast input data into the expected
   trusted format or to throw exceptions and avoid further processing
   of the data.
3. Raw unprocessed strings are never a trusted format and SHOULD be
   sanitized using a whitelist approach. 
4. Access to raw input strings is possible (not implemented yet) but MUST only be used in
   rare cases where security is ensured by other means. These cases will be subject to
   regular security audits.
   
## Examples

```php
// access integer value from query params (GET)
$q = $DIC->filter()->query();
$id = $q["id"]->int();

// access string value from post
$q = $DIC->filter()->post();
$str = $q["title"]->string();

// access cookie
$q = $DIC->filter()->cookie();
$cookie_val = $q["mycookie"]->string();

// html string from post
$forum_html_format = $DIC->filter()->stringFormat()->html(
	ilHtmlPurifierFactory::_getInstanceByType('frm_post')
);
$q = $DIC->filter()->post();
$forum_post = $q["forum_post"][10]->string($forum_html_format);
```
