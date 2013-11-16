## API

### Google books API
`https://www.googleapis.com/books/v1/volumes?q=${isbn_number}+isbn`

### Amazon API
#### ISBN Book Search
http://webservices.amazon.com/onca/xml?
  Service=AWSECommerceService
  &Operation=ItemLookup
  &ResponseGroup=Large
  &SearchIndex=All
  &IdType=ISBN
  &ItemId=076243631X
  &AWSAccessKeyId=[Your_AWSAccessKeyID]
  &AssociateTag=[Your_AssociateTag]
  &Timestamp=[YYYY-MM-DDThh:mm:ssZ]
  &Signature=[Request_Signature]

#### Reviews Search
http://webservices.amazon.com/onca/xml?
     Service=AWSECommerceService&
     AWSAccessKeyId=[AWS Access Key ID]&
     Operation=ItemLookup&
     ItemId=0316067938&
     ResponseGroup=Reviews&
     TruncateReviewsAt="256"&
     IncludeReviewsSummary="False"&
     Version=2011-08-01
     &Timestamp=[YYYY-MM-DDThh:mm:ssZ]
     &Signature=[Request Signature]