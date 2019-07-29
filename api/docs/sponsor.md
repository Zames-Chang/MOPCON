**Sponsor Api**
----

Returns json data about a sponsor information.


* **URL**
  * **贊助商資訊**

    `/api/sponsor`

* **Method:**

  `GET`
  
* **URL Params**

  None

* **Data Params**

  None

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** 
    ```
    {
      'success' : true,
      'message' : 'Success',
      'data' : [
          {
              'logo_path' : 'https://path/to/kkbox/logo', 
              'company' : 'kkbox', 
              'class' : 'BRUCE WAYNE'
          },
          {
              'logo_path' : 'https://path/to/aws/logo', 
              'company' : 'aws', 
              'class' : 'BRUCE WAYNE'
          }
      ]
    }
    ```
 
* **Error Response:**

  * **Code:** 400 BAD REQUEST <br />
    **Content:** 
    ```
    {
        'success' : false,
        'message' : 'Wrong protocol'
        'data'    : []
    }
    ```

* **Sample Call:**

  ```javascript
    $.ajax({
      url: "/api/sponsor",
      dataType: "json",
      type : "GET",
      success : function(r) {
        console.log(r);
      }
    });
  ```