# Importing from LeafData

Get the ZIP files or Table Dumps from LeafData.

## BOM, UTF-16

Sometimes the files are like this.
Some of the files are UTF-16

* https://www.dave-baker.com/2017/10/03/converting-a-utf-16-csv-to-utf-8-in-php/
* http://www.craiglotter.co.za/2010/03/07/how-to-convert-an-utf-16-file-to-an-utf-8-file-using-php/

## Extracting

The files may be delivered as `zip`, and internally they are labeled as `csv` files.
They may use a comma, they may use a TAB.


## Importing 

```
./extract.sh SOURCE_PATH
./import.sh SOURCE_PATH
./import-license.php
./import-product.php
./import-variety.php
./import-lot.php
./import-b2b-sale.php
./import-b2b-sale-item.php
./import-b2c-sale.php
./import-b2c-sale-item.php
```
