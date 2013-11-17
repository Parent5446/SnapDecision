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


### GoodReads
####Get review statistics given a list of ISBNs
https://www.goodreads.com/book/review_counts.json?isbns=0441172717%2C0141439602&key=3lgIZ9vQ7Gr882nHKep5A

####Get the reviews for a book given an ISBN
https://www.goodreads.com/book/isbn?isbn=0441172717&key=3lgIZ9vQ7Gr882nHKep5A&format=json

####Get the reviews for a book given a title string
https://www.goodreads.com/book/title?key=3lgIZ9vQ7Gr882nHKep5A&title=Hound+of+the+Baskervilles&format=json