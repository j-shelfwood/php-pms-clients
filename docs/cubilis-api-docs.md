# OTA Connectivity

```
Version 2.0 – August 2023
```

## Contents

-   1. Revision history
-   2. Introduction
-   3. Test account
-   4. Authentication
-   5. Room information
    -   5.1. OTA_HotelRoomListRQ
    -   5.2. OTA_HotelRoomListRS
-   6. Rates and availability
    -   6.1. OTA_HotelAvailNotifRQ
    -   6.2. OTA_HotelAvailNotifRS
-   7. Reservations
    -   7.1. Push
        -   7.1.1. OTA_HotelResRQ
        -   7.1.2. OTA_HotelResRS
    -   7.2. Pull
        -   7.2.1. OTA_HotelResRQ
        -   7.2.2. OTA_HotelResRS
-   8. Error
    -   8.1. Description
    -   8.2. Error list
        -   8.2.1. OTA_HotelRoomListRS
        -   8.2.2. OTA_HotelAvailNotifRS
        -   8.2.3. OTA_HotelResRS

## 1. Revision history

Date Description

April 2015 A new chapter “Base allocation” is added to the documentation

November 2015 A new chapter “Error List” is added to the documentation

February 2016 Add ResGuestRPH to OTA_HotelResRQ

October 2016 Add Error list

November 2016 Value Closed has been changed to Close for Status in
OTA_HotelAvailNotifRQ

January 2017 Add Start and End attributes to the RoomStay element

May 2017 Update reservation examples

September 2017 Update description of PurgeDate

January 2018 Add fallback information to OTA_HotelResRQ

May 2018 Information has been added to Test Account

July 2020 Add information about endpoints.

August 2022 Updated branding

November 2022 Fixed typos, removed Extras in OTA_HotelResRQ

December 2022 Base allocation deprecated

August 2023 Added Information in reservation [full & push] ResStatus

## 2. Introduction

This document will explain how Cubilis can be connected with a booking website.

Cubilis OTA Connectivity gives the possibility to receive updates about rates,
availability and restrictions from Cubilis and to insert reservations into Cubilis.

The xml messages are based on the OpenTravel Standard OTA2009a. Today we
don’t use all elements and attributes of the OTA standard, but we can add an
element or attribute without warning you in advance.

Some elements or attributes won’t be shown in the xml because the setting isn’t
active. If you want to receive the information, you can contact our technical
department. They will activate the setting and then you will receive the
corresponding element or attribute.

If you have any questions, please contact our technical department who is
responsible for the connections: **connectivity@stardekk.com****_._\*\*

## 3. Test account

During the developing stage a test account as will be provided.

Our technical department ( **connectivity@stardekk.com** ) will create the test
account and will provide you with credentials for the xml interface and for the Cubilis
web interface.

Cubilis will send requests to retrieve room information (OTA_HotelRoomListRQ) and
updates about rates, availability and restrictions (OTA_HotelAvailNotifRQ). You will
have to create 1 common endpoint or 2 separate endpoints for the requests. Please
provide them while you request a test account.

You can login into the web interface of Cubilis on **https://my.stardekk.com**
Here, you can view and change the settings (availability, price, restrictions). Each
time a change to the data is made, an update request will be sent to your system. In
Cubilis you can also check any reservations you’ve sent through the xml interface.

## 4. Authentication

```
Every message that is sent must be accompanied by a POS message. The POS
message contains information about the relevant hotel.
In the rest of this documentation we will only mention the location of POS.
```

Level Element Type Description

0 POS C Root Element.

1 Source C

##### 2

```
RequestorID M The ID of the PMS system or the username of the hotel.
```

@Type M (^) When Type = 1, ID contains the username of the hotel.
@ID M When Type = 2, ID contains the ID of the booking site.^
@MessagePassword O This attribute is mandatory when Type = 1. It contains
the password of the hotel.
C = collection, M = mandatory, O = optional, S = settings
<POS>

<Source>
<RequestorID Type="1" ID="info@stardekk.be" MessagePassword="U88m58W36" />
</Source>
<Source>
<RequestorID Type="2" ID=" 2 " />
</Source>
</POS>

## 5. Room information

### 5.1. OTA_HotelRoomListRQ

```
When a new hotel is connected, the roomtypes and rateplans must be connected in
Cubilis. On the basis of the IDs the roomtypes and rateplans can be mapped. The
OTA_HotelRoomListRS response contains the roomtype and rateplan IDs.
Cubilis will send the request to the endpoint you have provided.
```

Level Element Type Description

##### 0

```
OTA_HotelRoomListRQ C Root Element.
```

```
@Version M The version number of the protocol that is used to
connect to Cubilis. This document describes version
“2.0”.
```

```
@xmlns M The XML namespace that is used.
```

1 POS M Authentication (see page 6).

1 HotelRoomLists C Grouping of HotelRoomList.

2 HotelRoomList M
C = collection, M = mandatory, O = optional, S = settings

```
<?xml version="1.0" encoding="utf-8" ?>
<OTA_HotelRoomListRQ Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<POS>...</POS>
<HotelRoomLists>
<HotelRoomList/>
</HotelRoomLists>
</OTA_HotelRoomListRQ>
```

### 5.2. OTA_HotelRoomListRS

Level Element Type Description

##### 0

```
OTA_HotelRoomListRS C Root Element.
```

```
@Version M The version number of the protocol that is used to
connect to Cubilis. This document describes
version “2.0”.
```

```
@xmlns M The XML namespace that is used.
```

1 Success^ O^ Identicates that a valid response is returned.^

1 HotelRoomLists C Grouping of HotelRoomList.

##### 2

```
HotelRoomList M A list of all roomtypes and rateplans per hotel.
```

```
@HotelCode M The ID of the hotel.
```

3 RoomStays C Grouping of RoomStay.

##### 4

```
RoomStay C Grouping of RoomTypes en RatePlans.
Each roomtype will be presented in a new
RoomStay element.
```

5 RoomTypes C Grouping of Roomtype.

##### 6

```
RoomType C Information about the roomtype.
@IsRoom M Indicates if the roomtype is a room. There could be
a roomtype meeting room, then the value of
IsRoom is false.
@RoomID M The ID of the roomtype.
```

##### 7

```
RoomDescription M
```

```
@Name M The name of the roomtype.
```

5 Rateplans C Grouping of Rateplan.

##### 6

```
Rateplan C Information about the rateplan.
```

```
@RatePlanID M The ID of the rateplan.
```

```
@RatePlanName M The name of the rateplan.
C = collection, M = mandatory, O = optional, S = settings
```

<?xml version="1.0" encoding="utf-8"?>

<OTA_HotelRoomListRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<Success />
<HotelRoomLists>
<HotelRoomList HotelCode="HotelCode">
<RoomStays>
<RoomStay>
<RoomTypes>
<RoomType IsRoom="true" RoomID="RoomID1">
<RoomDescription Name="Name1" />
</RoomType>
</RoomTypes>
<RatePlans>
<RatePlan RatePlanID="RatePlanID1" RatePlanName="RatePlanName1" />
<RatePlan RatePlanID="RatePlanID2" RatePlanName="RatePlanName2" />
<RatePlan RatePlanID="RatePlanID3" RatePlanName="RatePlanName3" />
</RatePlans>
</RoomStay>
<RoomStay>
<RoomTypes>
<RoomType IsRoom="true" RoomID="RoomID2">
<RoomDescription Name="Name2" />
</RoomType>
</RoomTypes>
<RatePlans>
<RatePlan RatePlanID="RatePlanID4" RatePlanName="RatePlanName4" />
<RatePlan RatePlanID="RatePlanID5" RatePlanName="RatePlanName5" />
<RatePlan RatePlanID="RatePlanID6" RatePlanName="RatePlanName6" />
</RatePlans>
</RoomStay>
</RoomStays>
</HotelRoomList>
</HotelRoomLists>
</OTA_HotelRoomListRS>

## 6. Rates and availability

### 6.1. OTA_HotelAvailNotifRQ

```
Each time when data changes in Cubilis, this data will be sent to the booking
website. Only data that has been changed, will be forwarded.
Cubilis will send the request to the endpoint you have provided.
```

Level Element Type Description

##### 0

```
OTA_HotelAvailNotifRQ C Root Element.
```

```
@Version M The version number of the protocol that is used. The
version number for this request must be 2.0.
```

```
@xmlns M The XML namespace that is used.
```

1 POS M Authentication (see page 6).

##### 1

```
AvailStatusMessages C Grouping of AvailStatusMessage.
```

```
@BrandCode O The ID of Cubilis
```

##### 2

```
AvailStatusMessage C
@BookingLimit O The amount of free rooms.
```

##### 3

```
StatusApplicationControl M Information about the time period and the
roomtype.
```

```
@InvCode M The ID of the roomtype.
```

```
@Start M The start date must be the current date or later.
@End M The end date must be later than the start date. The
end date is not included in the time period.
```

```
@RatePlanID O The ID of the rateplan.
```

3 LengthsOfStay C Grouping of LengthOfStay.

##### 4

```
LengthOfStay O Option to define the length of stay, checkin or
checkout.
@Time M When Time > 0, Time contains information about
the length of stay.
When Time = 0 or Time = -1, Time contains
information about the checkin or checkout.
```

```
@MinMaxMessageType M When MinMaxMessageType = SetMinLOS and Time
> 0, Time indicates the minimum stay through.
```

```
The default value of the minimum stay through is 0.
When MinMaxMessageType = SetMinLOS and Time
= 0, checkin is possible and the date is open for
arrival.
When MinMaxMessageType = SetMinLOS and Time
= - 1, checkin is not possible and the date is closed
for arrival.
When MinMaxMessageType = SetMaxLOS and Time
> 0, Time indicates the maximum stay through.
The default value of the maximum stay is 99.
When MinMaxMessageType = SetMaxLOS and Time
= 0, checkout is possible and the date is open for
departure.
When MinMaxMessageType = SetMaxLOS and Time
= -1, checkout is not possible and the date is closed
for departure.
```

3 BestAvailableRates C Grouping of BestAvailablerate.

##### 4

```
BestAvailableRate O
@Amount M The price for the roomtype.
```

```
@RatePlanCode O Indicates if the price is for single use or for the
default number of persons of the roomtype. If the
attribute is present, it will have the value “Single”.
```

##### 3

```
RestrictionStatus O
```

```
@Status M When Status = Open, the roomtype is available.
When Status = Closed, the roomtype is not
available.
C = collection, M = mandatory, O = optional, S = settings
```

<?xml version="1.0" encoding="utf-8"?>

<OTA_HotelAvailNotifRQ Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<POS>...</POS>
<AvailStatusMessages>
<AvailStatusMessage BookingLimit="0">
<StatusApplicationControl InvCode="RoomID1" Start="1900- 01 - 01" End="1900- 01 - 01"
RatePlanID="RatePlanID1" />
<LengthsOfStay>
<LengthOfStay MinMaxMessageType="SetMinLOS" Time="0" />
<LengthOfStay MinMaxMessageType="SetMinLOS" Time="3" />
<LengthOfStay MinMaxMessageType="SetMaxLOS" Time="-1" />
<LengthOfStay MinMaxMessageType="SetMaxLOS" Time="5" />
</LengthsOfStay>
<BestAvailableRates>
<BestAvailableRate Amount="120.34" />
<BestAvailableRate Amount="110.34" RatePlanCode="Single"/>
</BestAvailableRates>
<RestrictionStatus Status="Open" />
</AvailStatusMessage>
<AvailStatusMessage BookingLimit="0">
<StatusApplicationControl InvCode="RoomID1" Start="1900- 01 - 01" End="1900- 01 - 01" />
<LengthsOfStay>
<LengthOfStay Time="1" MinMaxMessageType="SetMinLOS" />
</LengthsOfStay>
<BestAvailableRates>
<BestAvailableRate Amount="120.34" />
</BestAvailableRates>
<RestrictionStatus Status="Open" />
</AvailStatusMessage>
</AvailStatusMessages>
</OTA_HotelAvailNotifRQ>

### 6.2. OTA_HotelAvailNotifRS

Level Element Type Description

##### 0

```
OTA_HotelAvailNotifRS C Root Element.
```

```
@Version M The version number of the protocol that is used to
connect. This document describes version “2.0”.
@xmlns M The XML namespace that is used.
```

1 Success O Indicates that a valid response is returned.

1 Errors C Grouping of Error.

##### 2

```
Error M
```

```
@Code M The code of the error. The field can be empty.
```

```
@ShortText M The description of the error. The field can be empty.
```

```
@Type M The type of the error. The field can be empty.
C = collection, M = mandatory, O = optional; S = settings
```

```
<?xml version="1.0" encoding="utf-8"?>
<OTA_HotelAvailNotifRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<Success />
</OTA_HotelAvailNotifRS>
```

## 7. Reservations

### 7.1. Push

#### 7.1.1. OTA_HotelResRQ

```
The request must be sent to https://cubilis.eu/plugins/ota/reservations.aspx.
You have to use a fallback, in case you don’t receive a valid response. We advise to
implement the pull-method as fallback.
```

Level Element Type Description

##### 0

```
OTA_HotelResRQ C Root Element.
@Version M The version number of the protocol that is used to
connect.
This document describes version “2.0”.
```

```
@xmlns M The xml namespace that is used.^
```

1 POS M **Error! Reference source not found.**^ (see page
**Error! Bookmark not defined.** ).

1 HotelReservations C Grouping of HotelReservation.^

##### 2

```
HotelReservation O Information about the reservation.^
```

```
@CreateDateTime M The date on which the reservation was made.
Format yyyy-MM-ddTHH:mm:ss.
```

```
@CreatorID M The ID of the reservation.^
```

```
@ResStatus M The status of the reservation. The value can be:
“new”, “cancelled”, “modified” or “pending”. Pending
is the status of a reservation that still needs to be
confirmed(new) or cancelled.
```

3 RoomStays C Grouping of RoomStay.^

##### 4

```
RoomStay O Each RoomStay element contains 1 roomtype.^
```

```
@IndexNumber M The index of the RoomStay element.^
@Start O The arrival date. Format yyyy-MM-dd.^
```

```
@End O The departure date. Format yyyy-MM-dd.^
```

5 RoomTypes C Grouping of RoomType.^

6 RoomType M

```
@IsRoom M Indicates if the roomtype is a room. There could be
a roomtype meeting room, then the value of
IsRoom is false.
```

5 RatePlans C Grouping of RatePlan.^

##### 6

```
RatePlan M Information about the price per night.^
```

```
@EffectiveDate M The date of the relevant night. Format yyyy-MM-dd.^
```

```
@RatePlanID M The ID of the rateplan.^
@RatePlanName M The name of the rateplan.^
```

7 AdditionalDetails C Grouping of AdditionalDetail.^

##### 8

```
AdditionalDetail M
@Amount M The price for the relevant night.^
```

##### 5

```
Total M
```

```
@AmountAfterTax M The total price for the roomtype.^
```

##### 5

```
BasicPropertyInfo M Information about the hotel.^
@HotelCode M The ID of the hotel.^
```

5 Comments C Grouping of Comment.^

6 Comment C Grouping of Text.^

7 Text O

5 GuestCounts C Grouping of GuestCount.^

##### 6

```
GuestCount M The amount of persons that stays in the room.^
@AgeQualifyingCode M When AgeQualifyingCode = 1, Count contains the
amount of adults that stay in the room.
```

```
When AgeQualifyingCode = 2, Count contains the
amount of children that stay in the room.
When AgeQualifyingCode = 3, Count contains the
amount of babies that stay in the room.
```

```
@ Count M
```

5 ResGuestRPHs O Grouping of ResGuestRPH.^

##### 6

```
ResGuestRPH O
```

```
@RPH O The index of RPH.^
```

5 ServiceDetails C

6 Comments C Grouping of Comment.^

7 Comment C Grouping of Text.^

##### 6

```
Total M
@AmountAfterTax M The price for the service.^
```

3 ResGlobalInfo C Global information about the reservation.^

##### 4

```
TimeSpan M Information about the arrival and departure date.^
@Start M The arrival date and time. Format yyyy-MM-
ddTHH:mm.
```

```
@End M The departure date. Formaat yyyy-MM-dd.^
```

4 Comments C Grouping of Comment.^

5 Comment C Grouping of Text.^

6 Text M

4 Guarantee C

5 GuaranteesAccepted C Grouping of GuaranteeAccepted.^

6 GuaranteeAccepted C Grouping of PaymentCard.^

##### 7

```
PaymentCard O Information about the credit card.^
```

```
@CardCode M The type of the credit card. The value can be: “vi”
(Visa), “ax” (American Express), “dc” (Diners Club),
“mc” (Mastercard), “ds” (Discovery Card) or “jcb”
(JCB Card).
```

```
@CardNumber M The credit card number.^
```

```
@SeriesCode M The CVC code.^
@ExpireDate M The date of expiration of the credit card. Format
MMyy.
```

8 CardHolderName M The name of the credit card holder.^

##### 4

```
Total M
```

```
@AmountAfterTax M The total price of the reservation.^
```

4 Profiles C

5 ProfileInfo C Grouping of Profile.^

##### 6

```
Profile C Grouping of Customer.^
@RPH O The index of the RPH.^
```

7 Customer C Information about the customer.^

8 PersonName C

9 NamePrefix O Salutation of honorific.^

9 GivenName O The first name of the customer.^

9 SurName M The (last) name of the customer.^

##### 8

```
Telephone M
```

```
@PhoneNumber M The phone number of the customer.^
```

8 Email M The e-mail address of the customer.^

8 Address C The address of the customer.^

9 AddressLine M The address of the customer.^

9 CityName M The city where the customer lives.^

9 PostalCode M The postal code of the city.^

9 CountryName M The country where the customer lives.^

```
C = collection, M = mandatory, O = optional, S = settings
```

```
<?xml version="1.0" encoding="utf-8"?>
<OTA_HotelResRQ Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<POS>...</POS>
<HotelReservations>
<HotelReservation CreateDateTime="1900- 01 - 01T01:01:01" CreatorID="123"
ResStatus="New">
<RoomStays>
<RoomStay IndexNumber="1">
<RoomTypes>
<RoomType IsRoom="true" RoomID="1"></RoomType>
</RoomTypes>
<RatePlans>
<RatePlan EffectiveDate="2009- 11 - 20" RatePlanID="1" RatePlanName="Default
rate">
<AdditionalDetails>
<AdditionalDetail Amount="120.45" />
</AdditionalDetails>
</RatePlan>
<RatePlan EffectiveDate=" 2009 - 11 - 21 " RatePlanID="1" RatePlanName="Default
rate">
<AdditionalDetails>
<AdditionalDetail Amount=" 11 0.45" />
</AdditionalDetails>
</RatePlan>
</RatePlans>
<Total AmountAfterTax="230.90" />
<BasicPropertyInfo HotelCode="1" />
<Comments>
```

<Comment>
<Text>Customer comment on this room</Text>
</Comment>
</Comments>
<GuestCounts>
<GuestCount AgeQualifyingCode="1" Count="1" />
</GuestCounts>
</RoomStay>
</RoomStays>
<ResGlobalInfo>
<TimeSpan Start="2009- 11 - 20 T15:15" End="2009- 11 - 22 " />
<Comments>
<Comment>
<Text>My comments</Text>
</Comment>
</Comments>
<Guarantee>
<GuaranteesAccepted>
<GuaranteeAccepted>
<PaymentCard CardCode="MC" CardNumber="4111111111111111" SeriesCode="123"
ExpireDate="0111">
<CardHolderName>Christophe Devos</CardHolderName>
</PaymentCard >
</GuaranteeAccepted>
</GuaranteesAccepted>
</Guarantee>
<Total AmountAfterTax="242.90" />
<Profiles>
<ProfileInfo>
<Profile>
<Customer>
<PersonName>
<SurName>Christophe Devos</SurName>
</PersonName>
<Telephone PhoneNumber="050686869" />
<Email>christophe@stardekk.be</Email>
<Address>
<AddressLine>Altenatraat 2</AddressLine>
<CityName>Brugge</CityName>
<PostalCode>8000</PostalCode>
<CountryName>Belgium</CountryName>
</Address>
</Customer>
</Profile>
</ProfileInfo>
</Profiles>
</ResGlobalInfo>
</HotelReservation>
</HotelReservations>
</OTA_HotelResRQ>

#### 7.1.2. OTA_HotelResRS

Level Element Type Description

##### 0

```
OTA_HotelResRS C Root Element.
@Version M The version number of the protocol that is used to
connect. This document describes version “2.0”.
```

```
@xmlns M The xml namespace that is used.
```

1 Success O Indicates that a valid response is returned.

```
C = collection, M = mandatory, O = optional, S = settings
```

```
<?xml version="1.0" encoding="utf-8"?>
<OTA_HotelResRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<Success />
</OTA_HotelResRS>
```

### 7.2. Pull

#### 7.2.1. OTA_HotelResRQ

```
We use this message to retrieve the reservations that are present in your system.
Every new, modified, cancelled or pending reservation must be presented only once.
We send the request every 5 minutes. Because of this delay we advise to implement
the push method and use the pull method as fallback.
Cubilis will send the request to the endpoint you have provided.
```

Level Element Type Description

##### 0

```
OTA_HotelResRQ C Root Element.
```

```
@Version M The version number of the protocol that is used to
connect. This document describes version “2.0”.
```

```
@xmlns M The xml namespace that is used.
```

1 POS M **Error! Reference source not found.**^ (see page
**Error! Bookmark not defined.** ).

##### 1

```
UniqueID O
```

```
@Type M Only “RES” is allowed.
```

```
@ID M The ID of the reservation.
```

1 HotelReservations C A grouping of HotelReservation.

##### 2

```
HotelReservation M
```

```
@PurgeDate O When the attribute PurgeDate^ is present, all
reservations made on this date or later, will be sent
in the OTA_HotelResRS response.
When the attribute PurgeDate is omitted, all
reservations never sent to Cubilis before, will be
sent in the OTA_HotelResRS response.
C = collection, M = mandatory, O = optional, S = settings
```

```
<?xml version="1.0" encoding="utf-8"?>
<OTA_HotelResRQ Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<POS>...</POS>
<HotelReservations>
<HotelReservation />
</HotelReservations>
</OTA_HotelResRQ>
```

#### 7.2.2. OTA_HotelResRS

Level Element Type Description

##### 0

```
OTA_HotelResRS C Root Element.
@Version M The version number of the protocol that is used to
connect.
```

```
This document describes version “2.0”.
@xmlns M The xml namespace that is used.^
```

1 Success O Indicates that a valid response is returned.^

1 HotelReservations C A grouping of HotelReservation.^

##### 2

```
HotelReservation O Information about the reservation.^
```

```
@CreateDateTime M The date on which the reservation was made.
Format yyyy-MM-ddTHH:mm:ss.
```

```
@CreatorID M The ID of the reservation.^
```

```
@ResStatus M The status of the reservation. The value can be:
“new”, “cancelled”, “modified” or “pending”. Pending
is the status of a reservation that still needs to be
confirmed(new) or cancelled.
A reservation which has been cancelled or modified
doesn’t have to be preceded by a reservation with
status “new”.
```

##### 3

```
RoomStays C Grouping of RoomStay. For each room there will be
an element RoomStay.
```

##### 4

```
RoomStay O Each RoomStay element contains 1 roomtype.^
```

```
@IndexNumber M The index of the RoomStay element.^
```

```
@Start O The arrival date. Format yyyy-MM-dd.^
```

```
@End O The departure date. Format yyyy-MM-dd.^
```

5 RoomTypes C Grouping of RoomType.^

##### 6

```
RoomType M
```

```
@IsRoom M Indicates if the roomtype is a room. There could be a
roomtype meeting room, then the value of IsRoom is
false.
```

5 RatePlans C Grouping of RatePlan.^

##### 6

```
RatePlan M Information about the price per night.^
@EffectiveDate M The date of the relevant night. Format yyyy-MM-dd.^
```

```
@RatePlanID M The ID of the rateplan.^
```

```
@RatePlanName M The name of the rateplan.^
```

7 AdditionalDetails C Grouping of AdditionalDetail.^

##### 8

```
AdditionalDetail M
```

```
@Amount M The price for the relevant night.^
```

##### 5

```
Total M
```

```
@AmountAfterTax M The total price for the roomtype.^
```

##### 5

```
BasicPropertyInfo M Information about the hotel.^
@HotelCode M The ID of the hotel.^
```

5 Comments C Grouping of Comment.^

6 Comment C Grouping of Text.^

7 Text O

5 GuestCounts C Grouping of GuestCount.^

##### 6

```
GuestCount M The amount of persons that stay in the room.^
```

```
@AgeQualifyingCode M When AgeQualifyingCode^ = 1, Count contains the
amount of adults that stay in the room.
When AgeQualifyingCode = 2, Count contains the
amount of children that stay in the room.
When AgeQualifyingCode = 3, Count contains the
amount of babies that stay in the room.
```

```
@ Count M
```

6 ResGuestRPHs O Grouping of ResGuestRPH^

##### 7

```
ResGuestRPH O
@RPH O The index of the RPH.^
```

6 Comments C Grouping of Comment.^

7 Comment C Grouping of Text.^

6 Total M

```
@AmountAfterTax M The price for the service.^
```

3 ResGlobalInfo C Global information about the reservation.^

##### 4

```
TimeSpan M Information about the arrival and departure date.^
```

```
@Start M The arrival date and time. Format yyyy-MM-
ddTHH:mm.
```

```
@End M The departure date. Format yyyy-MM-dd.^
```

4 Comments C Grouping of Comment.^

5 Comment C Grouping of Text.^

6 Text M

4 Guarantee C

5 GuaranteesAccepted C Grouping of GuaranteeAccepted.^

6 GuaranteeAccepted C Grouping of PaymentCard.^

##### 7

```
PaymentCard O Information about the credit card.^
```

```
@CardCode M The type of the credit card. The value can be: “vi”
(Visa), “ax” (American Express), “dc” (Diners Club),
“mc” (Mastercard), “ds” (Discovery Card) or “jcb”
(JCB Card).
```

```
@CardNumber M The credit card number.^
```

```
@SeriesCode M The CVC code.^
```

```
@ExpireDate M The date of expiration of the credit card. Format
MMyy.
```

8 CardHolderName M The name of the credit card holder.^

##### 4

```
Total M
```

```
@AmountAfterTax M The total price of the reservation.^
```

4 Profiles C

5 ProfileInfo C Grouping of Profile.^

##### 6

```
Profile C Grouping of Customer.^
```

```
@RPH O The index of the RPH.^
```

7 Customer C Information about the customer.^

8 PersonName C

9 NamePrefix O Salutation of honorific.^

9 GivenName O The first name of the customer.^

9 SurName M The (last) name of the customer.^

##### 8

```
Telephone M
@PhoneNumber M The Phone number of the customer.^
```

8 Email M The e-mail address of the customer.^

8 Address C The address of the customer.^

9 AddressLine M The address of the customer.^

9 CityName M The city where the customer lives.^

9 PostalCode M The postal code of the city.^

9 CountryName M The country where the customer lives.^

```
C = collection, M = mandatory, O = optional, S = settings
```

```
<?xml version="1.0" encoding="utf-8"?>
<OTA_HotelResRS Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
<HotelReservations>
<HotelReservation CreateDateTime="1900- 01 - 01T01:01:01" CreatorID="123"
ResStatus="New">
<RoomStays>
<RoomStay IndexNumber="1" Start="2009- 11 - 20" End=" 2009 - 11 - 22 ">
<RoomTypes>
<RoomType IsRoom="true" RoomID="1"></RoomType>
</RoomTypes>
<RatePlans>
<RatePlan EffectiveDate="2009- 11 - 20" RatePlanID="1" RatePlanName="Default
rate">
<AdditionalDetails>
<AdditionalDetail Amount="120.45" />
</AdditionalDetails>
</RatePlan>
<RatePlan EffectiveDate="2009- 11 - 21 " RatePlanID="1" RatePlanName="Default
rate">
<AdditionalDetails>
<AdditionalDetail Amount=" 11 0.45" />
</AdditionalDetails>
</RatePlan>
</RatePlans>
<Total AmountAfterTax="230.90" />
<BasicPropertyInfo HotelCode="1" />
<Comments>
<Comment>
<Text>Customer comment on this room</Text>
</Comment>
</Comments>
<GuestCounts>
<GuestCount AgeQualifyingCode="1" Count="1" />
</GuestCounts>
</RoomStay>
</RoomStays>
```

<ResGlobalInfo>
<TimeSpan Start="2009- 11 - 20" End=" 2009 - 11 - 22 " />
<Comments>
<Comment>
<Text>My comments</Text>
</Comment>
</Comments>
<Guarantee>
<GuaranteesAccepted>
<GuaranteeAccepted>
<PaymentCard CardCode="MC" CardNumber="4111111111111111" SeriesCode="123"
ExpireDate="0111">
<CardHolderName>Christophe Devos</CardHolderName>
</PaymentCard >
</GuaranteeAccepted>
</GuaranteesAccepted>
</Guarantee>
<Total AmountAfterTax="242.90" />
<Profiles>
<ProfileInfo>
<Profile>
<Customer>
<PersonName>
<SurName>Christophe Devos</SurName>
</PersonName>
<Telephone PhoneNumber="050686869" />
<Email>christophe@stardekk.be</Email>
<Address>
<AddressLine>Altenatraat 2</AddressLine>
<CityName>Brugge</CityName>
<PostalCode>8000</PostalCode>
<CountryName>Belgium</CountryName>
</Address>
</Customer>
</Profile>
</ProfileInfo>
</Profiles>
</ResGlobalInfo>
</HotelReservation>
</HotelReservations>
</OTA_HotelResRS>

## 8. Error

### 8.1. Description

```
In case of an error, the error will be returned in the same document element name as
expected from the request.
For example, if the setting of the availability fails, the following message is returned
```

Level Element Type Description

##### 0

```
OTA_HotelAvailNotifRS C Root Element.
```

```
@Version M The version number of the protocol that is used to
connect. This document describes version “2.0”.
```

```
@xmlns M The xml namespace that is used.
```

1 Errors C Grouping of Error.

##### 2

```
Error M
```

```
@Code O The code of the error. This field can be empty.
```

```
@ShortText M The description of the error. This field can be empty.
@Type M The type of the error. This field can be empty.
C = collection, M = mandatory, O = optional, S = settings
```

```
<?xml version="1.0" encoding="utf-8"?>
<OTA_HotelAvailNotifRS xmlns="http://www.opentravel.org/OTA/2003/05" Version="2.0">
<Errors>
<Error Code="500" ShortText="EndDate can't be earlier then StartDate" Type=" 2 " />
</Errors>
</OTA_HotelAvailNotifRS>
```

### 8.2. Error list

```
If the request couldn’t be processed, an error should be returned in the response. At
least the following errors should be returned.
```

#### 8.2.1. OTA_HotelRoomListRS

Code Type ShortText

507 Authentication error Unknown username

Unknown username of the hotel. The value of the attribute ID in the element RequestorID is
incorrect.

508 Authentication error Invalid password

The password in the element RequestorID is incorrect.

#### 8.2.2. OTA_HotelAvailNotifRS

Code Type ShortText

507 Authentication error Unknown username

Unknown username of the hotel. The value of the attribute ID in the element RequestorID is
incorrect.

508 Authentication error Invalid password

The password in the element RequestorID is incorrect.

527 Configuration error Invalid InvCode

The InvCode is incorrect.

629 Configuration error Invalid RatePlanID

The RatePlanID is incorrect.

#### 8.2.3. OTA_HotelResRS

Code Type ShortText

507 Authentication error Unknown username

Unknown username of the hotel. The value of the attribute ID in the element RequestorID is
incorrect.

508 Authentication error Invalid password

The password in the element RequestorID is incorrect.
