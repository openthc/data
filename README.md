# Data

An Open Data Portal for Cannabis and tools for Converting and Importing said data.

This tool is designed to import data from BioTrack, GrowFlow, LeafData or METRC into a common schema.
Then you can run reports on this data-set using a front-end tool like [Metabase](https://www.metabase.com/)
 or [Apache Superset](https://superset.apache.org/).

## Converting CSV

OpenTHC has defined some "standard" CSV file Headers to use for data importing.
Typically, one can use existing software exports, update their header column names and then convert/import.
The names are not case sensitive.

```
Section_GUID
Section_Name
Section_Type
Variety_GUID
Variety_Name
Variety_Type
Product_GUID
Product_Name
Product_Type
Package_Unit_Weight
Package_Unit_Volume
Inventory_GUID
Inventory_Name
Crop_GUID
Crop_Name
Plant_GUID
Plant_Name
```

Crop and Plant are the same thing; we have both names to help thing about their usage uniquely.
A Crop is an identifier for one or more plants (or a whole field) but Plant explicitly means just a single plant.

Then just convert the objects out of the source document.

```shell
/bin/cli.php convert \
	--output-path output-data/convert-example/
	--source-file=./source-data/Inventory.csv --source-type=product-csv

/bin/cli.php convert \
	--output-path output-data/convert-example/
	--source-file=./source-data/Inventory.csv --source-type=section-csv

/bin/cli.php convert \
	--output-path output-data/convert-example/
	--source-file=./source-data/Inventory.csv --source-type=variety-csv

/bin/cli.php convert \
	--output-path output-data/convert-example/
	--source-file=./source-data/Inventory.csv --source-type=inventory-csv
```


## Loading BioTrack

We can load BioTrack data from either the API, CSV or SQL data.

```shell
./bin/import.php \
	--source-type=BioTrack-API \
	--source=$SOURCE_URI
	--object=crop \
	--output=OUTPUT_PATH
```


## Loading Akerna/LeafData/MJFreeway/MJPlatform Data

Get the ZIP files or Table Dumps from LeafData.
Put the LeafData CSV/Zip files in some origin directory (`./source-data/leafdata`).
Extract and prepare them as necessary.
Symlink those files into `./source-data/$NAME.csv`, using the name required by the import script.
Then remove the origin file, leaving an orphan symlink to track what's been completed.
And clean up when all the way done.

See `./bin/leafdata/extract.sh` for an automated process.


## Loading CCRS

Get the ZIP file dumps from the LCB and store them in something like `./source-data-YYYY-MM-DD`.
Use the `box-download.php` helper.
Then, from that source-data directory run `../bin/ccrs/import.php [OBJECT] | OUTPUT-[OBJECT].txt`


## Loading GrowFlow

The scripts in lib/Import/GrowFlow can import from the `CSV` exports or from collected `HAR` files.

```shell
./bin/import.php \
	--source=source-data/More_Vegging_Plants_export.csv  \
	--source-type=GrowFlow-CSV \
	--object=crop \
	--output=OUTPUT_PATH
```


## Loading Cultivera

The scripts in lib/Import/Cultivera can import from the `HAR` or `XLSX` exports.

```shell
./bin/import.php \
	--source=source-data/Inventory.xlsx  \
	--source-type=Cultivera-XLSX \
	--object=inventory \
	--output=OUTPUT_PATH
```


### BOM, UTF-16

Sometimes the files are like this.
Some of the files are UTF-16-LE.
Our pre-processing scripts will normalise them.

* https://www.dave-baker.com/2017/10/03/converting-a-utf-16-csv-to-utf-8-in-php/
* http://www.craiglotter.co.za/2010/03/07/how-to-convert-an-utf-16-file-to-an-utf-8-file-using-php/


### iconv

* Use iconv -f UTF-16LE -t UTF-8
iconv -f UTF-16LE -t UTF-8 <filename> -o <new-filename>


### Extracting

The files may be delivered as `zip`, and internally they are labeled as `csv` files.
They may use a comma, they may use a TAB.


----

# Data Information

Product Categories:
	All the Standard Numbers
	+Joints, BHO

Group by Sizes
	But find the Common Sizes/Size Groups First!

Facet: License
Facet: Product
Facet: Variety

## Retail Detail


# Data!

The data scripts make TSV files from the SQL.
Each TSV answers one question built around these "base facets"

  * Category: Plants, Wholesale, Retail, QA
  * An Inventory Type (5, 13, 28, etc)
  * A Date Delta - Daily, Weekly, Monthly, Q, Y

The answers are "business questions" - like price per gram or volume
Details are in the ./data-*.php scripts, the parameter is 'a'

Each Data category is answered by a PHP script ./data-*.php
Those scripts are called by the wrapper ./data.php, like this:

	./data.php wholesale d=w t=28 a=ppg-avg

That will in-turn execute code in the ./data-wholesale.php and pass the parameters in as $_GET values

Those $_GET values are used to control logic that will assemble the SQL in the form of

	SELECT (value-expression)
	FROM (necessary-tables)
		JOIN (may-need-one-or-more-of-these)
	WHERE (desired-constructed-filter)
		AND (additional-conditions-we-discover-to-remove-crap-data)

And then iterate the Date Delta spewing the results into a TSV file
Actually, the PHP scripts output to STDOUT which is redirected to capture the output.

## Shell Wrappers

The shell wrappers (data-*.sh) are designed to iterate over the "base facets"

	foreach Date
		foreach Type
			foreach OtherThing
				./data.php [category] d=Date t=Type a=ppg-avg \
					> ./webroot/pub/data/[predictable-path-here-somehow]


We kind of know these to be the base queries for the data that people want.
So we can pre-build these.

## Time to First Harvest
## Time from Harvest to Cure

##
Store-level summary by inventory type of what they paid for X and what
they charged for X where X is the # of units or grams sold during the
time period in question carry level of detail that allows drilldown to
a specific processor or producer

This should enable a bunch of stuff I'll add to the document later today

## Markup by County

Chocolate vs Cookies
Regions
Strains on Sales


## Business Intelligence Tools

* [eBay TSV Tools](https://github.com/eBay/tsv-utils)
* [Poli](https://news.ycombinator.com/item?id=20507592)
