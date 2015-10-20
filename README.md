Themecli
=======

Themecli is a PHP script compiled to executable using [Bamcompile](http://sourceforge.net/projects/bamcompile/files/bamcompile/). It is meant to be used to generate HTML documents by sending variables from your application to your PHP script. So that you can use another tool, like [wkhtmltopdf](http://github.com/wkhtmltopdf/wkhtmltopdf), to generate PDF files.

Download the ready-to-use tool clicking below (this is a pre-release):

[Download now](https://github.com/jesobreira/themecli/releases/download/1.0/themecli.exe)

Usage
--------

`themecli.exe <input php script> <output html file> <variables*>`

As:

* input php script: Your PHP script. You can use the variables that will be sent later directly (like echoing or manipulating).
* output html file: A HTML file where the output will be saved.
* variables: A base64-encoded JSON object with all your variables and its values (note that you can also send arrays, and loop on them on the script).

Practical example
------------------------

Kay... let's say you want to generate a bill. Don't worry, this is a very simple example.

Your PHP script (input) would be like:

```php
<h1>Bill</h1>
<?php
$total = 0;
echo "<p>".$client."</p>";
foreach($items as $item) {
  echo $item['name'].' - '.$item['value']."<br/>";;
  $total += $item['value'];
}
echo "Total: ".$total;
```

Then you run:
`themecli.exe yourfile.php result.html "[base64 encoded JSON]"`

Where your JSON would be (remember it must be base64 encoded):

`{"client":"John Doe","items":[{"name":"Ebook","value":1.99},{"name":"Internet access","value":49}]}`
