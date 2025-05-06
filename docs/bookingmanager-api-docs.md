# BillyPDS XML Protocol

```
Protocol specification version 1.0.3 –  11 ‐ 05 ‐ 2012 
By Tim Gerritsen <tg@billypds.com>
```

## VERSION HISTORY

### VERSION DATE CHANGES

0.1.0 08 ‐^04 ‐^2011 Initial^ version^
0.2.0 19 ‐^04 ‐^2011 Added^ booking^ cancel^ function^ &^ authentication
0.3.0 26 ‐^04 ‐^2011 Minimal^ document^ layout^ changes^ &^ test^ user^ added
0.4.0 18 ‐^05 ‐^2011 Changed^ apartment^ detail^ function^ (tac/tax/provider)
Added new function 'changes'
0.5.0 24 ‐^05 ‐^2011 Added^ version^ &^ info^ function.
Added data types
0.6.0 31 ‐^05 ‐^2011 Added^ unique^ identifier^ given^ by^ provider^ to^ all^ property^ exports
Changed urls to new vhost (old ones still work)
Added new function 'search'
Added images to property details
0.7.0 24 ‐^06 ‐^2011 Added^ new^ function^ 'cities'
0.7.1 14 ‐^07 ‐^2011 Small^ changes^ in^ 'info.xml'^ output.^ Tax^ is^ splitted^ in^ tourist^ and^ vat.
0.8.0 25 ‐^07 ‐^2011 Added^ new^ function^ 'providers'&^ deleted^ extended^ provider^ info^ in^ property^ details^
Added new function 'calendar'
0.8.1 04 ‐^08 ‐^2011 Deleted^ to^ property^ amenities^ (desks^ and^ couches)
Added^ 'city*id'^ input^ field^ for^ 'search.xml'
0.9.0 11 ‐^08 ‐^2011 Added^ fee^ and^ prepayment^ in^ functions^ 'info',^ 'calendar'^ and^ 'booking'
Added new error message  305 
0.9.5 16 ‐^08 ‐^2011 Deleted^ minimal^ and^ maximal^ days^ in^ provider^ seasons
Deleted 'hifi' property anemity
Added provider information, zipcode, area and active to property info (list.xml)
Added 'city' as possible view (details.xml)
Added 'freezer', 'connection_tv' and 'connection_internet' to the property amenities (details.xml)
Added tax type 'relative' (percentages) or 'fixed' (fixed amount) (details, calendar & providers)
Added default minimal & maximal nights in providers listing (providers.xml)
Added maximal nights to property info (list.xml)
Added 'providers' to changes.xml
0.9.6 30 ‐^08 ‐^2011 Added^ 'guests'^ to^ 'search.xml'^ input^ field
Added new function 'stay‐minimum'
Added 'stay‐minimum' value to calendar.xml
0.9.7 15 ‐^09 ‐^2011 Added^ 'arrival'^ and^ 'departure'^ attribute^ to^ booking^ result
Deleted 'notes' from provider details
0.9.8 03 ‐^11 ‐^2011 Added^ 'arrival'^ and^ 'tac'^ content^ to^ property^ details^ (possible^ overwrite^ of^ provider^ info)^
Added 'arrival' content to provider details (providers.xml), put 'tac' and 'arrival' in 'content' node.
Added 'country' to providers.xml
Added 'type' attribute to seasons in providers.xml (relative seasons means they are relative to the high
season (rackrate))
1.0.0 14 ‐^11 ‐^2011 Added^ new^ function^ 'booking_view'
Added new function 'booking_edit'
Added 'created' and 'modified' dates to booking functions
Added 'bookings' to changes.xml
Added API key authentication
Added 'balance_due' and rates including taxes inside 'info.xml'
Changed description 'booking identifier' property (provider identifier, not channel identifier)
Added 'approach' chapter to explain certain strategies
1.0.1 28 ‐^11 ‐^2011 Added^ new^ function^ 'booking_pending'
1.0.2 16 ‐^04 ‐^2012 Deprecated^ 'tourist^ tax'^ is^ now^ 'other'^ (other^ fees)in'booking*_.xml',^ 'details.xml',^ 'info.xml',^
'providers.xml' and 'calendar.xml'
Deprecated service/cleaning (is now cleaning*costs) in 'details.xml'
Added 'cleaning_costs' & 'deposit_costs' to 'details.xml' and 'providers.xml'
Changed prepayment, balance due and fee (only  1  in rate breakdown) in 'booking*_.xml' and 'info.xml'
Added attribute 'status' to property node in 'list.xml'. (Only when 'live' property is bookable)
Added new error code   405 
1.0.3 11 ‐^05 ‐^2012 Calendar.xml^ function^ is^ deprecated.

## USING THE API

### AUTHORIZATION

To authorize to the Billy system, you need an API key. This key needs to be send with every API request. An API key is  32 
characters long and only contains  0 ‐ 9  and a‐f. Use the GET or POST variable 'key' to send the API key.

### TESTING

BillyPDS provides a test environment. The new features can be tested here. Use the URL **_[http://xml‐test.billypds.com](http://xml‐test.billypds.com)_**
instead of _[http://xml.billypds.com](http://xml.billypds.com)_.

### ERROR MESSAGES

```
 200: Success
 300: No results
 301: Invalid input given
 302: Invalid property given
 303: Property is unavailable
 304: Booking not found
 305: Booking failed at provider
 306: Maximum amount of guests exceeded
 307: Only open or success bookings allowed to edit
 308: Invalid character encoding. Should be UTF‐ 8 
 403: Unauthorized access
 404: No such function
 405: Account is not in production
```

### EXAMPLE

HTTP reply:
<error code="303">Property is unavailable</error>

### DATA TYPES

**Data type Format Description**
datetime YYYY‐MM‐DD hh:mm:ss Date and time stamp
date YYYY‐MM‐DD Date stamp
integer # Number
float #.# Floating point number
string One or more readable characters
text Multiple characters (can also contain new lines)
boolean Either  1  or  0 
country 2 ‐character country code (ISO‐ 3166 ‐1)
cs\_\* _,_,_ Comma separated list of the given '_' type

## TECHNICAL APPROACH

### LIVE CHECK

If your website is only using the service of BillyPDS and doesn't connect to other sources, the live check would be the best
option. By using functions like _search.xml_ and _info.xml_ it's possible to retrieve rate and availability data real time.

### SYNC BY CHANGES

The other option is to use the _changes.xml_ function. This function returns all changed data since a certain time. You can
either check this function every few minutes, or request a callback mechanism which will send the output of this function to
a certain URL. See the _changes.xml_ function for more info about this.
When interpreting the output of this function, you'll need to use other functions as well. It will only tell you which ids are
touched.
The following list describes all the types of changes and the matching functions that should be called:
 Details: list.xml, details.xml
 Availability: availability.xml
 Rate: rates.xml, rate_anomaly.xml, rate_longstay.xml
 Stay_minimum: stay_minimum.xml
 Providers: providers.xml

## VERSION

This function is used to determine the current running version of BillyXML.

### INPUT

No input.

### RESULTS

Function will return the current version.

### DATA TYPES

### EXAMPLE

HTTP request:
POST /version.xml?key=e570f99745341a89e883c583a25b821c HTTP/1.
Host: xml.billypds.com
HTTP reply:
<version>1.0.3</version>
**Data type Name Description**
version /version Version number looks like x.y.z.
x = major version, will be only updated when rewriting the base
y = minor version, will be updated when new functions are added or old functions
has been changed.
z = small updates, no need to update the protocol

## PING

This is a very simple function to determine if the server is still up. It will retrieve the system time.

### INPUT

No input.

### RESULTS

Function will return a 'pong' element containing a current system time attribute.

### DATA TYPES

### EXAMPLE

```yaml
HTTP request:
POST /ping.xml?key=e570f99745341a89e883c583a25b821c HTTP/1.
Host: xml.billypds.com
HTTP reply:
<pong date="2011-04-08 15:48:16" />
```

| Data type | Name       | Description                   |
| --------- | ---------- | ----------------------------- |
| datetime  | /pong@date | Current server date and time. |

## LIST

This function returns the listing of all active apartments.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return a listing of the active apartments.

### DATA TYPES

| Data type | Name                            | Description                                                                              |
| --------- | ------------------------------- | ---------------------------------------------------------------------------------------- |
| integer   | /property@id                    | Unique property id.                                                                      |
| string    | /property@name                  | Property name                                                                            |
| integer   | /property@identifier            | Unique property identifier given by provider.                                            |
| boolean   | /property@active                | Whether the property is active or not. (If not, no child nodes)                          |
| string    | /property@status                | Status of the property (either 'ready' or 'live') (when ready, property is not bookable) |
| integer   | /property/provider@id           | Provider identifier                                                                      |
| string    | /property/provider@code         | Small textual identifier of the provider (abbreviation)                                  |
| string    | /property/provider@name         | Full name of the property provider                                                       |
| cs_float  | /property/location/gps          | GPS lat and long coordinates of the property                                             |
| string    | /property/location/address      | Property address                                                                         |
| string    | /property/location/zipcode      | Property zipcode                                                                         |
| country   | /property/location/city@country | Property Country code                                                                    |
| cs_float  | /property/location/city@gps     | GPS lat and long coordinates of the city                                                 |
| string    | /property/location/city         | City name                                                                                |
| string    | /property/location/area         | Area name                                                                                |
| cs_string | /property/type                  | Either 'leisure', 'business' or both.                                                    |
| integer   | /property/max_persons           | Maximum amount of guests possible                                                        |
| integer   | /property/minimal_nights        | Minimum nights this property needs to be booked                                          |
| integer   | /property/maximal_nights        | Maximum nights of booking (0 = infinite)                                                 |
| date      | /property/available_start       | First date this property is available (0000‐00‐00 means always)                          |
| date      | /property/available_end         | First date this property is unavailable (0000‐00‐00 means always)                        |
| date      | /property/created               | Creation date                                                                            |
| date      | /property/modified              | Modification date, will be updated when property details changes.                        |

### EXAMPLE

```xml
HTTP request:
POST /list.xml?key=e570f99745341a89e883c583a25b821c&id=1722,1723 HTTP/1.
Host: xml.billypds.com
HTTP reply:
<properties>
<property id="207" identifier="1722" name="Amstel Studio 1" active="1" status="live">
<provider id="1" code="PRN" name="Provider name" />
<location>
<gps>52.3614,4.8984</gps>

<address>Address 123</address>
<zipcode>1234</zipcode>
<city country="NL" gps="52.3614,4.8984">Amsterdam</city>
<area>jordaan</area>
</location>
<type>leisure,business</type>
<max_persons>2</max_persons>
<minimal_nights>3</minimal_nights>
<maximal_nights>0</maximal_nights>
<available_start>2010-04-16</available_start>
<available_end>2013-04-16</available_end>
<created>2010-04-16 14:43:55</created>
<modified>2011-01-06 15:54:15</modified>
</property>
<property id="1723" name="Amstel Studio 2" active="1" status="live">
<provider id="1" code="PRN" name="Provider name" />
<location>
<gps>52.3614,4.8984</gps>
<address>Address 123</address>
<zipcode>1234</zipcode>
<city country="NL" gps="52.3614,4.8984">Amsterdam</city>
<area>jordaan</area>
</location>
<type>leisure,business</type>
<max_persons>1</max_persons>
<minimal_nights>3</minimal_nights>
<maximal_nights>0</maximal_nights>
<available_start>2010-04-16</available_start>
<available_end>2013-04-16</available_end>
<created>2010-04-16 15:04:46</created>
<modified>2010-12-16 12:42:14</modified>
</property>
<property id="1724" name="Amstel Studio 3" active="0" />
</properties>
```

## DETAILS

This function returns the details of all active apartments.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return all the details of the active apartments.

### DATA TYPES

| Data type | Name                                  | Description                                                                                             |
| --------- | ------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| integer   | /property@id                          | Unique property id.                                                                                     |
| string    | /property@name                        | Property name                                                                                           |
| integer   | /property@identifier                  | Unique property identifier given by provider.                                                           |
| integer   | /property/floor                       | Floor number                                                                                            |
| integer   | /property/stairs                      | Stairs inside the property                                                                              |
| float     | /property/size                        | Size in squared metres                                                                                  |
| integer   | /property/bedroom                     | Amount of bedrooms                                                                                      |
| integer   | /property/single_bed                  | Amount of single beds                                                                                   |
| integer   | /property/double_bed                  | Amount of double beds                                                                                   |
| integer   | /property/single_sofa                 | Amount of single sleeping sofas                                                                         |
| integer   | /property/double_sofa                 | Amount of double sleeping sofas                                                                         |
| integer   | /property/single_bunk                 | Amount of single bunk beds                                                                              |
| integer   | /property/bathrooms                   | Amount of bathrooms                                                                                     |
| integer   | /property/toilets                     | Amount of toilets                                                                                       |
| boolean   | /property/elevator                    | Elevator available                                                                                      |
| string    | /property/view                        | Either 'water','street','forest' or 'city'                                                              |
| string    | /property/internet                    | Either 'cable','wifi','usb' or 'none'                                                                   |
| string    | /property/internet_connection         | Either 'highspeed','mobile' or 'none'                                                                   |
| string    | /property/parking                     | Either 'private','free','public' or 'none'                                                              |
| boolean   | /property/airco                       | Air‐conditioning available                                                                              |
| boolean   | /property/fans                        | Fans available                                                                                          |
| boolean   | /property/balcony                     | Balcony available                                                                                       |
| boolean   | /property/patio                       | Property has a patio                                                                                    |
| boolean   | /property/garden                      | Property has a garden                                                                                   |
| boolean   | /property/roof_terrace                | Property has a roof terrace                                                                             |
| string    | /property/tv                          | Either 'flatscreen','normal' or 'none'                                                                  |
| string    | /property/tv_connection               | Either 'satellite','cable' or 'none'                                                                    |
| string    | /property/dvd                         | Either 'surround','dvd','cd' or 'none'                                                                  |
| boolean   | /property/computer                    | Computer is available                                                                                   |
| boolean   | /property/printer                     | Printer is available                                                                                    |
| boolean   | /property/iron                        | Iron & board is available                                                                               |
| boolean   | /property/dishwasher                  | Dishwasher is available                                                                                 |
| boolean   | /property/oven                        | Oven is available                                                                                       |
| boolean   | /property/microwave                   | Microwave is available                                                                                  |
| boolean   | /property/grill                       | Grill is available                                                                                      |
| boolean   | /property/hob                         | Hob is available                                                                                        |
| boolean   | /property/fridge                      | Fridge is available                                                                                     |
| boolean   | /property/freezer                     | Freezer is available                                                                                    |
| boolean   | /property/washingmachine              | Washing machine is available                                                                            |
| boolean   | /property/dryer                       | Dryer is available                                                                                      |
| boolean   | /property/toaster                     | Toaster is available                                                                                    |
| boolean   | /property/kettle                      | Kettle is available                                                                                     |
| boolean   | /property/coffeemachine               | Coffee machine is available                                                                             |
| integer   | /property/bathtub                     | Amount of regular bathtubs                                                                              |
| integer   | /property/jacuzzi                     | Amount of Jacuzzis                                                                                      |
| integer   | /property/shower_regular              | Amount of regular showers                                                                               |
| integer   | /property/shower_steam                | Amount of steam showers                                                                                 |
| string    | /property/swimmingpool                | Either 'private','public' or 'none'                                                                     |
| string    | /property/sauna                       | Either 'private','public' or 'none'                                                                     |
| boolean   | /property/hairdryer                   | Hair dryer is available                                                                                 |
| boolean   | /property/entresol                    | Property has a entresol                                                                                 |
| boolean   | /property/wheelchair_friendly         | Property is wheelchair friendly                                                                         |
| boolean   | /property/smoking_allowed             | Smoking is allowed inside the property                                                                  |
| boolean   | /property/pets_allowed                | Pets are allowed inside the property                                                                    |
| boolean   | /property/supplies/coffee             | Coffee is available upon arrival                                                                        |
| boolean   | /property/supplies/tea                | Tea is available upon arrival                                                                           |
| boolean   | /property/supplies/milk               | Milk is available upon arrival                                                                          |
| boolean   | /property/supplies/sugar              | Sugar is available upon arrival                                                                         |
| boolean   | /property/supplies/dishwasher_tablets | Dishwasher tables are available upon arrival                                                            |
| boolean   | /property/service/linen               | Linen are provided upon arrival                                                                         |
| boolean   | /property/service/towels              | Towels are provided upon arrival                                                                        |
| boolean   | /property/service/cleaning            | Property will be cleaned before and maybe during stay. Set to 1 when cleaning costs is filled in.       |
| float     | /property/cleaning_costs              | Extra cleaning costs for this property                                                                  |
| float     | /property/deposit_costs               | Deposit costs for this property                                                                         |
| float     | /property/tax/tourist                 | [ **DEPRECATED** ] New name is 'other'.                                                                 |
| float     | /property/tax/other                   | Amount of other fees (percentage in case of type 'relative', else a fixed amount)                       |
| string    | /property/tax/other@type              | Either 'relative' or 'fixed'. (same as provider settings)                                               |
| float     | /property/tax/vat                     | Amount of VAT tax (percentage in case of type 'relative', else a fixed amount)                          |
| string    | /property/tax/vat@type                | [ **DEPRECATED** ] Always 'relative'                                                                    |
| string    | /property/content/short               | Short description of the property                                                                       |
| text      | /property/content/full                | Full description of the property                                                                        |
| text      | /property/content/area                | Description about the area of the property                                                              |
| text      | /property/content/arrival             | (optional) arrival information overwrite If set, it will overwrite the default provider arrival content |
| text      | /property/content/tac                 | (optional) Terms and conditions overwrite If set, it will overwrite the default provider tac content.   |
| string    | /property/image@name                  | Image filename                                                                                          |
| url       | /property/image@url                   | Location of the image                                                                                   |
| text      | /property/image                       | Image description                                                                                       |
| integer   | /property/provider@id                 | Provider identifier                                                                                     |
| string    | /property/provider@code               | Small textual identifier of the provider (abbreviation)                                                 |
| string    | /property/provider@name               | Full name of the property provider                                                                      |

## EXAMPLE

```xml
HTTP request:
POST /details.xml?key=e570f99745341a89e883c583a25b821c &id=1722 HTTP/1.
Host: xml.billypds.com
HTTP reply:
<properties>
<property id="207" identifier="1722" name="Amstel Studio 1">
<provider id="1" code="PRN" name="Provider name" />
<floor>0</floor>
<stairs>0</stairs>
<size>0</size>
<bedrooms>0</bedrooms>
<single_bed>0</single_bed>
<double_bed>1</double_bed>
<single_sofa>0</single_sofa>
<double_sofa>0</double_sofa>
<single_bunk>0</single_bunk>
<bathrooms>1</bathrooms>
<toilets>1</toilets>
<elevator>0</elevator>
<view>street</view>
<internet>wifi</internet>
<internet_connection>highspeed</internet_connection>
<parking>public</parking>
<airco>0</airco>
<fans>0</fans>
<balcony>0</balcony>
<patio>0</patio>
<garden>0</garden>
<roof_terrace>0</roof_terrace>
<tv>flatscreen</tv>
<tv_connection>cable</tv_connection>
<dvd>dvd</dvd>
<computer>0</computer>
<printer>0</printer>
<iron>1</iron>
<dishwasher>0</dishwasher>
<oven>1</oven>
<microwave>1</microwave>
<grill>0</grill>
<hob>0</hob>
<fridge>1</fridge>
<freezer>1</freezer>
<washingmachine>0</washingmachine>
<dryer>0</dryer>
<toaster>1</toaster>
<kettle>1</kettle>
<coffeemachine>1</coffeemachine>
<bathtub>0</bathtub>
<jacuzzi>0</jacuzzi>
<shower_regular>1</shower_regular>
<shower_steam>0</shower_steam>
<swimmingpool>none</swimmingpool>
<sauna>none</sauna>
<hairdryer>1</hairdryer>
<entresol>0</entresol>
<wheelchair_friendly>0</wheelchair_friendly>
<smoking_allowed>0</smoking_allowed>
<pets_allowed>0</pets_allowed>
<supplies>
<coffee>0</coffee>

<tea>0</tea>
<milk>0</milk>
<sugar>0</sugar>
<dishwasher_tablets>0</dishwasher_tablets>
</supplies>
<service>
<linen>1</linen>
<towels>1</towels>
<cleaning>1</cleaning>
</service>
<cleaning_costs>25.00</cleaning_costs>
<deposit_costs>200.00</deposit_costs>
<tax>
<vat type="relative">11.0</vat>
<tourist type="relative">6.0</tourist>
<other type="relative">6.0</tourist>
</tax>
<content>
<short><![CDATA[High quality lower ground floor studio, located near the Utrechtsestraat and Rembrandt square.]]></short>
<full><![CDATA[New high quality lower ground floor studio, located in on the corner of the eclectic Utrechtsestraat and close to the vibrant
Rembrandt square.]]></full>
<area><![CDATA[The apartment is located ...]]></area>
<arrival ><![CDATA[Address information..]]></arrival >
</content>
<images>
<image name="image1.jpg" url="http://www.billypds.com/data/property/real/image1.jpg">Image 1 description</image>
<image name="image2.jpg" url="http://www.billypds.com/data/property/real/image2.jpg">Image 2 description</image>
</images>
</property>
</properties>
```

## CITIES

This function is used to retrieve all city information

### INPUT

No input required.

### RESULTS

Function will return all city information.

### DATA TYPES

| Data type | Name          | Description                              |
| --------- | ------------- | ---------------------------------------- |
| integer   | /city@id      | Unique city id.                          |
| cs_float  | /city@gps     | GPS lat and long coordinates of the city |
| string    | /city@country | Country code of the property             |
| string    | /city         | City name of the property                |

### EXAMPLE

```xml
HTTP request:
POST /cities.xml?key=e570f99745341a89e883c583a25b821c HTTP/1.
Host: xml.billypds.com
HTTP reply:
<cities>
<city id="1" gps="52.3731,4.89235" country="NL">Amsterdam</city>
<city id="2" gps="48.8566,2.35107" country="FR">Paris</city>
<city id="3" gps="41.0048,28.9764" country="TR">Istanbul</city>
<city id="4" gps="41.4027,2.16831" country="ES">Barcelona</city>
</cities>
```

## PROVIDERS

This function is used to retrieve all provider information

### INPUT

Optional comma‐separated ID list of providers. (not required)

### RESULTS

Function will return all provider information.

### DATA TYPES

| Data type | Name                      | Description                                                                              |
| --------- | ------------------------- | ---------------------------------------------------------------------------------------- |
| integer   | /provider@id              | Unique provider identifier                                                               |
| string    | /provider@code            | Small textual identifier of the provider (abbreviation)                                  |
| string    | /provider@name            | Full name of the property provider                                                       |
| string    | /provider/company         | Company name                                                                             |
| string    | /provider/contact         | Contact name                                                                             |
| string    | /provider/email           | Email address                                                                            |
| string    | /provider/address         | Company address                                                                          |
| string    | /provider/zipcode         | Company zipcode                                                                          |
| string    | /provider/city            | Company city                                                                             |
| string    | /provider/country         | Company country                                                                          |
| string    | /provider/nr_vat          | Company VAT number                                                                       |
| string    | /provider/nr_iban         | Company IBAN number                                                                      |
| string    | /provider/website         | Website address                                                                          |
| string    | /provider/skype           | Skype name                                                                               |
| string    | /provider/phone           | Phone number                                                                             |
| string    | /provider/mobile          | Mobile phone number                                                                      |
| string    | /provider/fax             | Fax number                                                                               |
| integer   | /provider/minimal_nights  | Default minimum nights for it's properties                                               |
| integer   | /provider/maximal_nights  | Default maximum nights (0 = infinite)                                                    |
| text      | /provider/content/arrival | Arrival information of the provider                                                      |
| text      | /provider/content/tac     | Terms and conditions of the provider                                                     |
| float     | /provider/cleaning_costs  | Default provider cleaning costs                                                          |
| float     | /provider/deposit_costs   | Default provider deposit costs                                                           |
| float     | /provider/tax/tourist     | [ **DEPRECATED** ] New name is 'other'.                                                  |
| float     | /provider/tax/other       | Amount of other fees (percentage in case of type 'relative', else a fixed amount)        |
| string    | /provider/tax/other@type  | Either 'relative' or 'fixed'.                                                            |
| float     | /provider/tax/vat         | Amount of VAT tax (percentage in case of type 'relative', else a fixed amount)           |
| string    | /provider/tax/vat@type    | Either 'relative' or 'fixed'.                                                            |
| float     | /provider/fee             | Channel fee percentage                                                                   |
| float     | /provider/prepayment      | Prepayment percentage                                                                    |
| string    | /provider/seasons@type    | Either 'relative' or 'fixed'. (are the mid and low seasons relative to the high season?) |

## EXAMPLE

```xml
HTTP request:
POST /providers.xml? key=e570f99745341a89e883c583a25b821c &id=1 HTTP/1.
Host: xml.billypds.com
HTTP reply:
<providers>
<provider id="1" code="PRN" name="Provider name">
<company>Provider company name</company>
<contact>Tim Gerritsen</contact>
<email>tg@billypds.com</email>

<address>Billystreet 1</address>
<zipcode>1234AB</zipcode>
<city>Amsterdam</city>
<country>The Netherlands</country>
<nr_vat>NL1234567890</nr_vat>
<nr_iban>P5678923</nr_iban>
<website>http://www.billypds.com</website>
<skype>billy.corp</skype>
<phone>+31-(6)-1234568</phone>
<mobile>+3163122342</mobile>
<fax>00313012312</fax>
<minimal_nights>3</minimal_nights>
<maximal_nights>0</maximal_nights>
<content>
<arrival>Arrival information text here</arrival>
<tac>terms and conditions text here</tac>
</content>
<cleaning_costs>25.00</cleaning_costs>
<deposit_costs>200.00</deposit_costs>
<tax>
<vat type="relative">6</vat>
<tourist type="relative">5</tourist>
<other type="relative">5</other>
</tax>
<fee>20</fee>
<prepayment>17.5</prepayment>
<seasons type="relative">
<season type="high" name="high">
<start month="4" day="7" />
<end month="10" day="17" />
<percentage>100</percentage>
</season>
</seasons>
</provider>
</providers>
```

## SEARCH

This function is used to search for certain properties.

### INPUT

Certain filter options described below.

### RESULTS

Function will return all the active apartments matched with the given filter.

### DATA TYPES

Current possible filter options (more on request):

| Data type | Name      | Description                                        |
| --------- | --------- | -------------------------------------------------- |
| string    | city      | City name                                          |
| integer   | city_id   | City identifier                                    |
| string    | area      | Area name                                          |
| integer   | guests    | Number of guests                                   |
| integer   | bedrooms  | Amount of bedrooms                                 |
| integer   | bathrooms | Amount of bathrooms                                |
| string    | internet  | Either 'wifi', 'cable' or 'usb'                    |
| date      | arrival   | Arrival date (filters only available apartments)   |
| date      | departure | Departure date (filters only available apartments) |

Output data types:

| Data type | Name                 | Description                                  |
| --------- | -------------------- | -------------------------------------------- |
| integer   | /property@id         | Unique property id                           |
| string    | /property@name       | Property name                                |
| integer   | /property@identifier | Unique property identifier given by provider |

### EXAMPLE

```http
POST /search.xml?key=e570f99745341a89e883c583a25b821c&arrival=2011-12-01&departure=2011-12-05&city=amsterdam HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<properties>
    <property id="207" identifier="1722" name="Amstel Studio 1" />
    <property id="208" identifier="1723" name="Amstel Studio 2" />
</properties>
```

## RATES

This function returns the rates of all active apartments.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return all the rates of the active apartments.

### DATA TYPES

| Data type  | Name                      | Description                                  |
| ---------- | ------------------------- | -------------------------------------------- |
| integer    | /rate@property_id         | Unique property id                           |
| integer    | /rate@property_identifier | Unique property identifier given by provider |
| cs_integer | /rate/days                | Weekdays (0 = Sunday, 6 = Saturday)          |
| string     | /rate/season              | Season type                                  |
| float      | /rate/value               | Rate value in euros                          |

### EXAMPLE

```http
POST /rates.xml?key=e570f99745341a89e883c583a25b821c&id=1723 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<rates>
    <rate property_id="208" property_identifier="1722">
        <days>1,2,3,4</days>
        <season>high</season>
        <value>250.00</value>
    </rate>
    <rate property_id="208" property_identifier="1722">
        <days>1,2,3,4</days>
        <season>mid</season>
        <value>250.00</value>
    </rate>
    <rate property_id="208" property_identifier="1722">
        <days>1,2,3,4</days>
        <season>low</season>
        <value>250.00</value>
    </rate>
</rates>
```

## RATE ANOMALY

This function returns the rate anomalies (special prices, discounts) of all active apartments.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return all the rate anomalies of the active apartments.

### DATA TYPES

| Data type | Name                              | Description                                         |
| --------- | --------------------------------- | --------------------------------------------------- |
| integer   | /rate_anomaly@property_id         | Unique property id                                  |
| integer   | /rate_anomaly@property_identifier | Unique property identifier given by provider        |
| integer   | /rate_anomaly@anomaly_id          | Unique rate anomaly id                              |
| date      | /rate_anomaly/start               | First date of the anomaly                           |
| date      | /rate_anomaly/end                 | Last date of the anomaly (including)                |
| float     | /rate_anomaly/percentage          | Percentage relative to the current rate of that day |

### EXAMPLE

```http
POST /rate_anomaly.xml?key=e570f99745341a89e883c583a25b821c&id=1723 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<rate_anomalies>
    <rate_anomaly property_id="208" property_identifier="1723" anomaly_id="23560">
        <start>2011-04-11</start>
        <end>2011-04-12</end>
        <percentage>110</percentage>
    </rate_anomaly>
    <rate_anomaly property_id="208" property_identifier="1723" anomaly_id="23559">
        <start>2011-04-07</start>
        <end>2011-04-08</end>
        <percentage>115</percentage>
    </rate_anomaly>
</rate_anomalies>
```

## RATE LONGSTAY

This function returns the longstay discounts of all active apartments.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return all the longstay discounts of the active apartments.

### DATA TYPES

| Data type | Name                          | Description                                                |
| --------- | ----------------------------- | ---------------------------------------------------------- |
| integer   | /longstay@property_id         | Unique property id                                         |
| integer   | /longstay@property_identifier | Unique property identifier given by provider               |
| integer   | /longstay/nights              | Amount of nights                                           |
| float     | /longstay/percentage          | Percentage relative to the current rate of the entire stay |

### EXAMPLE

```http
POST /rate_longstay.xml?key=e570f99745341a89e883c583a25b821c&id=1723 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<longstays>
    <longstay property_id="208" property_identifier="1723">
        <nights>7</nights>
        <percentage>90</percentage>
    </longstay>
    <longstay property_id="208" property_identifier="1723">
        <nights>14</nights>
        <percentage>80</percentage>
    </longstay>
    <longstay property_id="208" property_identifier="1723">
        <nights>21</nights>
        <percentage>70</percentage>
    </longstay>
</longstays>
```

## STAY MINIMUM

This function returns the minimum nights values of certain apartments that differs in time. The start and end date is
depending on the arrival date of a certain booking.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return all the minimum nights data of the active apartments.

### DATA TYPES

| Data type | Name                              | Description                                           |
| --------- | --------------------------------- | ----------------------------------------------------- |
| integer   | /stay_minimum@property_id         | Unique property id                                    |
| integer   | /stay_minimum@property_identifier | Unique property identifier given by provider          |
| integer   | /stay_minimum@minimum_id          | Unique minimum stay id                                |
| date      | /stay_minimum/start               | First night of the minimum stay difference            |
| date      | /stay_minimum/end                 | Last night of the minimum stay difference (including) |
| integer   | /stay_minimum/nights              | Minimum nights for this date                          |

### EXAMPLE

```http
POST /stay_minimum.xml?key=e570f99745341a89e883c583a25b821c&id=183 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<stay_minima>
    <stay_minimum property_id="183" property_identifier="2022" minimum_id="23560">
        <start>2011-10-01</start>
        <end>2011-10-05</end>
        <nights>5</nights>
    </stay_minimum>
    <stay_minimum property_id="183" property_identifier="2022" minimum_id="23561">
        <start>2011-10-06</start>
        <end>2011-10-10</end>
        <nights>7</nights>
    </stay_minimum>
</stay_minima>
```

## AVAILABILITY

This function returns the availability of all active apartments.

### INPUT

Optional comma‐separated ID list of apartments. (not required)

### RESULTS

Function will return all the availability of the active apartments.

### DATA TYPES

| Data type | Name                             | Description                                       |
| --------- | -------------------------------- | ------------------------------------------------- |
| integer   | /unavailable@property_id         | Unique property id                                |
| integer   | /unavailable@property_identifier | Unique property identifier given by provider      |
| integer   | /unavailable@availability_id     | Unique availability id                            |
| date      | /unavailable/start               | First date of the unavailability range            |
| date      | /unavailable/end                 | Last date of the unavailability range (including) |
| datetime  | /unavailable/modified            | Last modification timestamp                       |

### EXAMPLE

```http
POST /availability.xml?key=e570f99745341a89e883c583a25b821c&id=1723 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<availability>
    <unavailable property_id="208" property_identifier="1723" availability_id="14108">
        <start>2011-07-16</start>
        <end>2011-07-19</end>
        <modified>2011-03-25 15:31:07</modified>
    </unavailable>
    <unavailable property_id="208" property_identifier="1723" availability_id="14109">
        <start>2011-09-01</start>
        <end>2011-09-09</end>
        <modified>2011-03-25 15:31:07</modified>
    </unavailable>
    <unavailable property_id="208" property_identifier="1723" availability_id="14110">
        <start>2012-01-20</start>
        <end>2012-01-23</end>
        <modified>2011-04-08 03:13:02</modified>
    </unavailable>
</availability>
```

## CREATE BOOKING

This function is used to save a booking.

### INPUT

Booking information.

### RESULTS

Function will return the saved booking.

### DATA TYPES

| Data type | Name                         | Description                                                  |
| --------- | ---------------------------- | ------------------------------------------------------------ |
| integer   | /booking@id                  | Unique booking id                                            |
| string    | /booking@identifier          | Full unique booking identifier                               |
| string    | /booking@provider_identifier | Unique 3rd party booking identifier (provider)               |
| date      | /booking@arrival             | Arrival date of booking                                      |
| date      | /booking@departure           | Departure date of booking                                    |
| string    | /booking/name@last           | Last name of the customer                                    |
| string    | /booking/email               | Customer's email address                                     |
| string    | /booking/address_1           | Address line 1                                               |
| string    | /booking/address_2           | Address line 2                                               |
| string    | /booking/city                | City name of the customer                                    |
| string    | /booking/country             | Country code (ISO 3166-1)                                    |
| string    | /booking/phone               | Customer's phone number                                      |
| integer   | /booking/amount_adults       | Amount of adults booked                                      |
| integer   | /booking/amount_childs       | Amount of children booked                                    |
| time      | /booking/time_arrival        | Estimated time of arrival                                    |
| string    | /booking/flight              | Optional flight number                                       |
| text      | /booking/notes               | Booking notes                                                |
| integer   | /booking/property@id         | Booked property identifier                                   |
| integer   | /booking/property@identifier | Booked property identifier given by provider                 |
| string    | /booking/property            | Booked property name                                         |
| string    | /booking/status              | Either 'open' or 'error'                                     |
| string    | /booking/message             | Only set on 'error'. Describes what went wrong               |
| float     | /booking/rate/total          | Total rate of the booking (excluding discounts and taxes)    |
| float     | /booking/rate/final          | Final rate of the booking (including discounts, excl. taxes) |
| float     | /booking/rate/tax@total      | Amount of total tax calculated using the final rate          |
| float     | /booking/rate/tax/tourist    | [**DEPRECATED**] Now called 'other'                          |
| float     | /booking/rate/tax/other      | Amount of other fees calculated using the final rate         |
| float     | /booking/rate/tax/vat        | Amount of VAT tax calculated using the final rate            |
| float     | /booking/rate/tax/final      | Final rate of the booking (including taxes)                  |
| float     | /booking/rate/prepayment     | Prepayment rate of this booking                              |
| float     | /booking/rate/balance_due    | Balance due (final – prepayment)                             |
| float     | /booking/rate/fee            | Channel fee of this booking                                  |
| date      | /booking/created             | Creation date                                                |
| date      | /booking/modified            | Modification date, updated when booking details change       |

### EXAMPLE

````http
POST /booking_create.xml?key=e570f99745341a89e883c583a25b821c HTTP/1.1
Host: xml.billypds.com
Content-Type: application/x-www-form-urlencoded

start=2012-05-22
end=2012-05-25
name_first=tim
name_last=Gerritsen
email=tim@mannetje.org
address_1=chassestraat 18
address_2=
city=amsterdam
country=NL
phone=+31617260066
amount_adults=2
amount_childs=0
time_arrival=14:00
flight=WZ2237
notes=
property_id=2

HTTP reply:
<booking id="16" identifier="BILL-16-AMSLOC-1723-2012-05-22" provider_identifier="Provider-1234-54353" arrival="2012-05-22" departure="2012-05-25">
    <name first="tim" last="gerritsen" />
    <email>tim@mannetje.org</email>
    <address_1>chassestraat 18</address_1>
    <address_2></address_2>
    <city>amsterdam</city>
    <country>NL</country>
    <phone>+31617260066</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>0</amount_childs>
    <time_arrival>14:00</time_arrival>
    <flight>WZ2237</flight>
    <notes></notes>
    <property id="209" identifier="1723">Amstel Studio 2</property>
    <status>open</status>
    <rate>
        <total>550</total>
        <final>500</final>
        <tax total="55">
            <tourist>30</tourist>
            <other>30</other>
            <vat>25</vat>
            <final>555</final>
        </tax>
        <fee>115</fee>
        <prepayment>55</prepayment>
        <balance_due>455</balance_due>
    </rate>
    <created>2011-11-03 21:45:46</created>
    <modified>2011-11-03 21:45:46</modified>
</booking>

## VIEW BOOKING

This function is used to view a certain booking.

### INPUT

Booking id.

### RESULTS

Function will return the saved booking.

### DATA TYPES

| Data type | Name                         | Description                                                  |
| --------- | ---------------------------- | ------------------------------------------------------------ |
| integer   | /booking@id                  | Unique booking id                                            |
| string    | /booking@identifier          | Unique 3rd party booking identifier (provider)               |
| date      | /booking@arrival             | Arrival date of booking                                      |
| date      | /booking@departure           | Departure date of booking                                    |
| string    | /booking/name@last           | Last name of the customer                                    |
| string    | /booking/email               | Customer's email address                                     |
| string    | /booking/address_1           | Address line 1                                               |
| string    | /booking/address_2           | Address line 2                                               |
| string    | /booking/city                | City name of the customer                                    |
| string    | /booking/country             | Country code (ISO 3166-1)                                    |
| string    | /booking/phone               | Customer's phone number                                      |
| integer   | /booking/amount_adults       | Amount of adults booked                                      |
| integer   | /booking/amount_childs       | Amount of children booked                                    |
| time      | /booking/time_arrival        | Estimated time of arrival                                    |
| string    | /booking/flight              | Optional flight number                                       |
| text      | /booking/notes               | Booking notes                                                |
| integer   | /booking/property@id         | Booked property identifier                                   |
| integer   | /booking/property@identifier | Booked property identifier given by provider                 |
| string    | /booking/property            | Booked property name                                         |
| string    | /booking/status              | Either 'success','open' or 'error'                           |
| string    | /booking/message             | Only set on 'error'. Describes what went wrong               |
| float     | /booking/rate/total          | Total rate of the booking (excluding discounts and taxes)    |
| float     | /booking/rate/final          | Final rate of the booking (including discounts, excl. taxes) |
| float     | /booking/rate/tax@total      | Amount of total tax calculated using the final rate          |
| float     | /booking/rate/tax/tourist    | [**DEPRECATED**] Now called 'other'                          |
| float     | /booking/rate/tax/other      | Amount of other fees calculated using the final rate         |
| float     | /booking/rate/tax/vat        | Amount of VAT tax calculated using the final rate            |
| float     | /booking/rate/tax/final      | Final rate of the booking (including taxes)                  |
| float     | /booking/rate/prepayment     | Prepayment rate of this booking                             |
| float     | /booking/rate/balance_due    | Balance due (final – prepayment)                            |
| float     | /booking/rate/fee            | Channel fee of this booking                                 |
| date      | /booking/created             | Creation date                                                |
| date      | /booking/modified            | Modification date, updated when booking details change       |

### EXAMPLE

```http
POST /booking_view.xml?key=e570f99745341a89e883c583a25b821c HTTP/1.1
Host: xml.billypds.com
id=16

HTTP reply:
<booking id="16" identifier="BILL-16-AMSLOC-1723-2012-05-22" provider_identifier="Provider-1234-54353" arrival="2012-05-22" departure="2012-05-25">
    <name first="tim" last="gerritsen" />
    <email>tim@mannetje.org</email>
    <address_1>chassestraat 18</address_1>
    <address_2></address_2>
    <city>amsterdam</city>
    <country>NL</country>
    <phone>+31617260066</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>1</amount_childs>
    <time_arrival>14:00</time_arrival>
    <flight>WZ2237</flight>
    <notes></notes>
    <property id="209" identifier="1723">Amstel Studio 2</property>
    <status>open</status>
    <rate>
        <total>550</total>
        <final>500</final>
        <tax total="55">
            <tourist>30</tourist>
            <other>30</other>
            <vat>25</vat>
            <final>555</final>
        </tax>
        <fee>115</fee>
        <prepayment>55</prepayment>
        <balance_due>455</balance_due>
    </rate>
    <created>2011-11-03 21:45:46</created>
    <modified>2011-11-03 21:45:46</modified>
</booking>

## EDIT BOOKING

This function is used to alter a booking. Booking status has to be either 'open' or 'success'.

### INPUT

Booking id and the fields to modify.

### RESULTS

Function will return the modified booking.

### DATA TYPES

| Data type | Name                           | Description                                                |
| --------- | ------------------------------ | ---------------------------------------------------------- |
| integer   | /booking@id                    | Unique booking id                                          |
| string    | /booking@identifier            | Unique 3rd party booking identifier (provider)             |
| date      | /booking@arrival               | Arrival date of booking                                    |
| date      | /booking@departure             | Departure date of booking                                  |
| string    | /booking/name@last             | Last name of the customer                                  |
| string    | /booking/email                 | Customer's email address                                   |
| string    | /booking/address_1             | Address line 1                                             |
| string    | /booking/address_2             | Address line 2                                             |
| string    | /booking/city                  | City name of the customer                                  |
| string    | /booking/country               | Country code (ISO 3166-1)                                  |
| string    | /booking/phone                 | Customer's phone number                                    |
| integer   | /booking/amount_adults           | Amount of adults booked                                    |
| integer   | /booking/amount_childs           | Amount of children booked                                    |
| time      | /booking/time_arrival          | Estimated time of arrival                                  |
| string    | /booking/flight                  | Optional flight number                                     |
| text      | /booking/notes                   | Booking notes                                              |
| integer   | /booking/property@id             | Booked property identifier                                 |
| integer   | /booking/property@identifier     | Booked property identifier given by provider               |
| string    | /booking/property              | Booked property name                                       |
| string    | /booking/status                    | Either 'open' or 'error'                                   |
| string    | /booking/message                   | Only set on 'error'. Describes what went wrong             |
| float     | /booking/rate/total              | Total rate of the booking (excluding discounts and taxes)  |
| float     | /booking/rate/final              | Final rate of the booking (including discounts, excl. taxes)|
| float     | /booking/rate/tax@total          | Amount of total tax calculated using the final rate        |
| float     | /booking/rate/tax/tourist          | [**DEPRECATED**] Now called 'other'                        |
| float     | /booking/rate/tax/other          | Amount of other fees calculated using the final rate       |
| float     | /booking/rate/tax/vat            | Amount of VAT tax calculated using the final rate            |
| float     | /booking/rate/tax/final          | Final rate of the booking (including taxes)                |
| float     | /booking/rate/prepayment         | Prepayment rate of this booking                           |
| float     | /booking/rate/balance_due        | Balance due (final – prepayment)                          |
| float     | /booking/rate/fee                | Channel fee of this booking                               |
| date      | /booking/created               | Creation date                                              |
| date      | /booking/modified                | Modification date, updated when booking details change      |

### EXAMPLE

```http
POST /booking_edit.xml?key=e570f99745341a89e883c583a25b821c HTTP/1.1
Host: xml.billypds.com
Content-Type: application/x-www-form-urlencoded

id=16
end=2012-05-26
amount_childs=1

HTTP reply:
<booking id="16" identifier="BILL-16-AMSLOC-1723-2012-05-22" provider_identifier="Provider-1234-54353" arrival="2012-05-22" departure="2012-05-25">
    <name first="tim" last="gerritsen" />
    <email>tim@mannetje.org</email>
    <address_1>chassestraat 18</address_1>
    <address_2></address_2>
    <city>amsterdam</city>
    <country>NL</country>
    <phone>+31617260066</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>1</amount_childs>
    <time_arrival>14:00</time_arrival>
    <flight>WZ2237</flight>
    <notes></notes>
    <property id="209" identifier="1723">Amstel Studio 2</property>
    <status>open</status>
    <rate>
        <total>550</total>
        <final>500</final>
        <tax total="55">
            <tourist>30</tourist>
            <other>30</other>
            <vat>25</vat>
            <final>555</final>
        </tax>
        <fee>115</fee>
        <prepayment>55</prepayment>
        <balance_due>455</balance_due>
    </rate>
    <created>2011-11-03 21:45:46</created>
    <modified>2011-11-03 21:52:15</modified>
</booking>

## PENDING BOOKING

When a booking is not finalized yet but needs to stay alive a little longer, it's possible to change the booking status to
'pending'. This means the expire date of this booking will be set to 48 hours instead of 30 minutes (when status is 'open').

### INPUT

Booking id retrieved by create booking.

### RESULTS

Function will return the pended booking.

### DATA TYPES

| Data type | Name                           | Description                                                |
| --------- | ------------------------------ | ---------------------------------------------------------- |
| integer   | /booking@id                    | Unique booking id                                          |
| integer   | /booking@identifier            | Unique 3rd party booking identifier (provider)             |
| date      | /booking@arrival               | Arrival date of booking                                    |
| date      | /booking@departure             | Departure date of booking                                  |
| string    | /booking/name@first            | First name of the customer                                 |
| string    | /booking/name@last             | Last name of the customer                                  |
| string    | /booking/email                 | Customer's email address                                   |
| string    | /booking/address_1             | Address line 1                                             |
| string    | /booking/address_2             | Address line 2                                             |
| string    | /booking/city                  | City name of the customer                                  |
| string    | /booking/country               | Country code (ISO 3166-1)                                  |
| string    | /booking/phone                 | Customer's phone number                                    |
| integer   | /booking/amount_adults         | Amount of adults booked                                    |
| integer   | /booking/amount_childs         | Amount of children booked                                  |
| time      | /booking/time_arrival          | Estimated time of arrival                                  |
| string    | /booking/flight                | Optional flight number                                     |
| text      | /booking/notes                 | Booking notes                                              |
| integer   | /booking/property@id           | Booked property identifier                                 |
| integer   | /booking/property@identifier   | Booked property identifier given by provider               |
| string    | /booking/property              | Booked property name                                       |
| string    | /booking/status                | Either 'pending' or 'error'                                |
| float     | /booking/rate/total            | Total rate of the booking (excluding discounts and taxes)  |
| float     | /booking/rate/final            | Final rate of the booking (including discounts, excl. taxes)|
| float     | /booking/rate/tax@total        | Amount of total tax calculated using the final rate        |
| float     | /booking/rate/tax/tourist      | [**DEPRECATED**] Now called 'other'                        |
| float     | /booking/rate/tax/other        | Amount of other fees calculated using the final rate       |
| float     | /booking/rate/tax/vat          | Amount of VAT tax calculated using the final rate          |
| float     | /booking/rate/tax/final        | Final rate of the booking (including taxes)                |
## DATA TYPES

**Data type Name Description**
integer /booking@id Unique booking id.
integer /booking@identifier Unique 3 rd party booking identifier (provider)
date /booking@arrival Arrival date of booking
date /booking@departure Departure date of booking
string /booking/name@first First name of the customer
string /booking/name@last Last name of the customer
string /booking/email Customer's email address
string /booking/address_1 Address line 1
string /booking/address_2 Address line 2
string /booking/city City name of the customer
string /booking/country Country code (ISO 3166 ‐1)
string /booking/phone Customer's phone number
integer /booking/amount_adults Amount of adults booked
integer /booking/amount_childs Amount of children booked
time /booking/time_arrival Estimated time of arrival
string /booking/flight Optional flight number
text /booking/notes Booking notes
integer /booking/property@id Booked property identifier
integer /booking/property@identifier Booked property identifier given by provider.
string /booking/property Booked property name
string /booking/status Either 'pending' or 'error'
float /booking/rate/total Total rate of the booking (excluding any discounts, excluding taxes)
float /booking/rate/final Final rate of the booking (including any discounts, excluding taxes)
float /booking/rate/tax@total Amount of total tax calculated using the final rate.
float /booking/rate/tax/tourist [ **DEPRECATED** ] Now called 'other'
float /booking/rate/tax/other Amount of other fees calculated using the final rate.
float /booking/rate/tax/vat Amount of vat tax calculated using the final rate.

## EXAMPLE

HTTP request:
POST /booking_pending.xml?key=e570f99745341a89e883c583a25b821c&id=16 HTTP/1.1
Host: xml.billypds.com
HTTP reply:
<booking id="16" identifier="BILL-16-AMSLOC-1723-2012-05-22" provider_identifier="Provider-1234-54353" arrival="2012-05-22" departure="2012-05-25">
<name first="tim" last="gerritsen" />
<email>tim@mannetje.org</email>
<address_1>chassestraat 18</address_1>
<address_2></address_2>
<city>amsterdam</city>
<country>NL</country>
<phone>+31617260066</phone>
<amount_adults>2</amount_adults>
<amount_childs>0</amount_childs>
<time_arrival>14:00</time_arrival>
<flight>WZ2237</flight>
<notes></notes>
<property id="209" identifier="1723">Amstel Studio 2</property>
<status>pending</status>
<rate>
<total>550</total>
<final>500</final>
<tax total="55">
<tourist>30</tourist>
<other>30</other>
<vat>25</vat>
<final>555</final>
</tax>
<fee>115</fee>
<prepayment>55</prepayment>
<balance_due>455</balance_due>
</rate>
<created>2011-11-03 21:45:46</created>
<modified>2011-11-03 21:55:28</modified>
</booking>

## FINALIZE BOOKING

This function is used to finalize a booking.

### INPUT

Booking id retrieved by create booking.

### RESULTS

Function will return the finalized booking.

### DATA TYPES

| Data type | Name                           | Description                                                |
| --------- | ------------------------------ | ---------------------------------------------------------- |
| integer   | /booking@id                    | Unique booking id                                          |
| integer   | /booking@identifier            | Unique 3rd party booking identifier (provider)             |
| date      | /booking@arrival               | Arrival date of booking                                    |
| date      | /booking@departure             | Departure date of booking                                  |
| string    | /booking/name@first            | First name of the customer                                 |
| string    | /booking/name@last             | Last name of the customer                                  |
| string    | /booking/email                 | Customer's email address                                   |
| string    | /booking/address_1             | Address line 1                                             |
| string    | /booking/address_2             | Address line 2                                             |
| string    | /booking/city                  | City name of the customer                                  |
| string    | /booking/country               | Country code (ISO 3166-1)                                  |
| string    | /booking/phone                 | Customer's phone number                                    |
| integer   | /booking/amount_adults         | Amount of adults booked                                    |
| integer   | /booking/amount_childs         | Amount of children booked                                  |
| time      | /booking/time_arrival          | Estimated time of arrival                                  |
| string    | /booking/flight                | Optional flight number                                     |
| text      | /booking/notes                 | Booking notes                                              |
| integer   | /booking/property@id           | Booked property identifier                                 |
| integer   | /booking/property@identifier   | Booked property identifier given by provider               |
| string    | /booking/property              | Booked property name                                       |
| string    | /booking/status                | Either 'success' or 'error'                                |
| float     | /booking/rate/total            | Total rate of the booking (excluding discounts and taxes)  |
| float     | /booking/rate/final            | Final rate of the booking (including discounts, excl. taxes)|
| float     | /booking/rate/tax@total        | Amount of total tax calculated using the final rate        |
| float     | /booking/rate/tax/tourist      | [**DEPRECATED**] Now called 'other'                        |
| float     | /booking/rate/tax/other        | Amount of other fees calculated using the final rate       |
| float     | /booking/rate/tax/vat          | Amount of VAT tax calculated using the final rate          |
| float     | /booking/rate/tax/final        | Final rate of the booking (including taxes)                |
| float     | /booking/rate/prepayment       | Prepayment rate of this booking                           |
| float     | /booking/rate/balance_due      | Balance due (final – prepayment)                          |
| float     | /booking/rate/fee              | Channel fee of this booking                               |
| date      | /booking/created               | Creation date                                              |
| date      | /booking/modified              | Modification date, updated when booking details change      |

### EXAMPLE

```http
POST /booking_finalize.xml?key=e570f99745341a89e883c583a25b821c&id=16 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<booking id="16" identifier="BILL-16-AMSLOC-1723-2012-05-22" provider_identifier="Provider-1234-54353" arrival="2012-05-22" departure="2012-05-25">
    <name first="tim" last="gerritsen" />
    <email>tim@mannetje.org</email>
    <address_1>chassestraat 18</address_1>
    <address_2></address_2>
    <city>amsterdam</city>
    <country>NL</country>
    <phone>+31617260066</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>0</amount_childs>
    <time_arrival>14:00</time_arrival>
    <flight>WZ2237</flight>
    <notes></notes>
    <property id="209" identifier="1723">Amstel Studio 2</property>
    <status>success</status>
    <rate>
        <total>550</total>
        <final>500</final>
        <tax total="55">
            <tourist>30</tourist>
            <other>30</other>
            <vat>25</vat>
            <final>555</final>
        </tax>
        <fee>115</fee>
        <prepayment>55</prepayment>
        <balance_due>455</balance_due>
    </rate>
    <created>2011-11-03 21:45:46</created>
    <modified>2011-11-03 21:55:28</modified>
</booking>

## CANCEL BOOKING

This function is used to cancel a booking.

### INPUT

Booking id.

### RESULTS

Function will return the cancelled booking.

### DATA TYPES

| Data type | Name                         | Description                                                  |
| --------- | ---------------------------- | ------------------------------------------------------------ |
| integer   | /booking@id                  | Unique booking id                                            |
| string    | /booking@identifier          | Unique 3rd party booking identifier (provider)               |
| date      | /booking@arrival             | Arrival date of booking                                      |
| date      | /booking@departure           | Departure date of booking                                    |
| string    | /booking/name@first          | First name of the customer                                   |
| string    | /booking/name@last           | Last name of the customer                                    |
| string    | /booking/email               | Customer's email address                                     |
| string    | /booking/address_1           | Address line 1                                               |
| string    | /booking/address_2           | Address line 2                                               |
| string    | /booking/city                | City name of the customer                                    |
| string    | /booking/country             | Country code (ISO 3166-1)                                    |
| string    | /booking/phone               | Customer's phone number                                      |
| integer   | /booking/amount_adults       | Amount of adults booked                                      |
| integer   | /booking/amount_childs       | Amount of children booked                                    |
| time      | /booking/time_arrival        | Estimated time of arrival                                    |
| string    | /booking/flight              | Optional flight number                                       |
| text      | /booking/notes               | Booking notes                                                |
| integer   | /booking/property@id         | Booked property identifier                                   |
| integer   | /booking/property@identifier | Booked property identifier given by provider                 |
| string    | /booking/property            | Booked property name                                         |
| string    | /booking/status              | Either 'cancelled' or 'error'                                |
| float     | /booking/rate/total          | Total rate of the booking (excluding discounts and taxes)    |
| float     | /booking/rate/final          | Final rate of the booking (including discounts, excl. taxes) |
| float     | /booking/rate/tax@total      | Amount of total tax calculated using the final rate          |
| float     | /booking/rate/tax/tourist    | [**DEPRECATED**] Now called 'other'                          |
| float     | /booking/rate/tax/other      | Amount of other fees calculated using the final rate         |
| float     | /booking/rate/tax/vat        | Amount of VAT tax calculated using the final rate            |
| float     | /booking/rate/tax/final      | Final rate of the booking (including taxes)                  |
| float     | /booking/rate/prepayment     | Prepayment rate of this booking                             |
| float     | /booking/rate/balance_due    | Balance due (final – prepayment)                            |
| float     | /booking/rate/fee            | Channel fee of this booking                                  |
| date      | /booking/created             | Creation date                                                |
| date      | /booking/modified            | Modification date, updated when booking details change        |

### EXAMPLE

```http
POST /booking_cancel.xml?key=e570f99745341a89e883c583a25b821c&id=16 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<booking id="16" identifier="BILL-16-AMSLOC-1723-2012-05-22" provider_identifier="Provider-1234-54353" arrival="2012-05-22" departure="2012-05-25">
    <name first="tim" last="gerritsen" />
    <email>tim@mannetje.org</email>
    <address_1>chassestraat 18</address_1>
    <address_2></address_2>
    <city>amsterdam</city>
    <country>NL</country>
    <phone>+31617260066</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>0</amount_childs>
    <time_arrival>14:00</time_arrival>
    <flight>WZ2237</flight>
    <notes></notes>
    <property id="209" identifier="1723">Amstel Studio 2</property>
    <status>cancelled</status>
    <rate>
        <total>550</total>
        <final>500</final>
        <tax total="55">
            <tourist>30</tourist>
            <other>30</other>
            <vat>25</vat>
            <final>555</final>
        </tax>
        <fee>115</fee>
        <prepayment>55</prepayment>
        <balance_due>455</balance_due>
    </rate>
    <created>2011-11-03 21:45:46</created>
    <modified>2011-11-03 21:55:28</modified>
</booking>

## CHANGES

This function will check if something has changed (or added) after a certain date.
It's possible to request to use our callback mechanism. If needed please send us the URL to connect to. This URL should
output "OK" when received successfully.

### INPUT

Last checked date & time. (Format: YYYY-MM-DD hh:mm:ss)

### RESULTS

Function will return one of the three possible changes (details, availability, rate or providers) and the belonging property
ids.

### DATA TYPES

| Data type | Name            | Description                                                |
| --------- | --------------- | ---------------------------------------------------------- |
| string    | /change@type    | Either 'details', 'availability', 'rate', 'providers' or 'bookings' |
| integer   | /change@amount  | Amount of properties that have changed                     |
| cs_integer| /change@ids     | Modified property identifiers                              |
| datetime  | /change@time    | Modification date of the last modified property            |

### EXAMPLE

```http
POST /changes.xml?key=e570f99745341a89e883c583a25b821c&time=2010-01-01%2012:53:50 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<changes>
    <change type="details" amount="3" ids="1,2,3" time="2011-05-15 12:54:22">
    <change type="availability" amount="1" ids="4" time="2011-05-01 12:54:22">
    <change type="rate" amount="2" ids="3,6" time="2011-05-15 13:54:22">
    <change type="providers" amount="1" ids="8" time="2011-06-15 11:25:31">
    <change type="bookings" amount="1" ids="412" time="2011-05-01 11:54:22">
</changes>
````

## INFO

This function is used to retrieve information using the given arrival and departure date.

### INPUT

Property ids, arrival date and departure date.

### RESULTS

Function will return the rate and availability information.

### DATA TYPES

| Data type | Name                                | Description                                                           |
| --------- | ----------------------------------- | --------------------------------------------------------------------- |
| date      | /info@arrival                       | Arrival date (same as input)                                          |
| date      | /info@departure                     | Departure date (same as input)                                        |
| integer   | /info@nights                        | Amount of nights of the stay                                          |
| integer   | /info/property@id                   | Property identifier                                                   |
| integer   | /info/property@max_persons          | Maximum amount of people can book this property                       |
| boolean   | /info/property@available            | Whether or not this property is available                             |
| float     | /info/property/rate/total           | Total rate of the stay (excluding discounts and taxes)                |
| float     | /info/property/rate/final           | Final rate of the stay (including discounts, excl. taxes)             |
| float     | /info/property/rate/tax@total       | Amount of total tax calculated using the final rate                   |
| float     | /info/property/rate/tax/tourist     | [**DEPRECATED**] Now called 'other'                                   |
| float     | /info/property/rate/tax/other       | Amount of other fees calculated using the final rate                  |
| string    | /info/property/rate/tax/other@type  | Either 'relative' or 'fixed'                                          |
| float     | /info/property/rate/tax/other@value | Amount of other fees (percentage if relative, fixed amount otherwise) |
| float     | /info/property/rate/tax/vat         | Amount of VAT tax calculated using the final rate                     |
| string    | /info/property/rate/tax/vat@type    | [**DEPRECATED**] Always 'relative'                                    |
| float     | /info/property/rate/tax/vat@value   | Amount of VAT tax (percentage if relative, fixed amount otherwise)    |
| float     | /info/property/rate/tax/final       | Final rate of the stay (including taxes)                              |
| float     | /info/property/rate/fee             | Channel fee of this stay                                              |
| float     | /info/property/rate/prepayment      | Prepayment rate of this stay                                          |
| float     | /info/property/rate/balance_due     | Balance due (final – prepayment)                                      |

### EXAMPLE

```http
POST /info.xml?key=e570f99745341a89e883c583a25b821c&id=1&arrival=2011-12-01&departure=2011-12-04 HTTP/1.1
Host: xml.billypds.com

HTTP reply:
<info arrival="2012-12-01" departure="2012-12-05" nights="4">
    <property id="1" max_persons="2" available="0">
        <rate>
            <total>550</total>
            <final>500</final>
            <tax total="55">
                <tourist type="relative" value="6">30</tourist>
                <other type="relative" value="6">30</other>
                <vat type="relative" value="5">25</vat>
                <prepayment>55.5</prepayment>
                <balance_due>499.5</balance_due>
                <final>555</final>
            </tax>
            <fee>115</fee>
            <prepayment>55</prepayment>
            <balance_due>500</balance_due>
        </rate>
    </property>
</info>
```
