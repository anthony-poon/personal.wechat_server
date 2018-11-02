This is an template used for building web applications. It is build using on Symfony 4 framework. It has basic layout and user management interface.

**API Documentation:**

    GET     =>  Read
    POST    =>  Create
    PUT     =>  Update
    DELETE  =>  Delete          
            
**Route: /api/users**

GET

    Read a user with id without admin privilege, can only get account with ROLE_USER
    Json Body: null
    Example:
        /api/users?id=1                         Get user with id 1
        /api/users?fullName=Anthony+Poon        Get user with fullName exactly match "Anthony Poon"
    TODO:
        Accept wild card name
        Account muliple id
    
POST

    Register a new user without admin privilege
    Json Body: {
        "username": string      min=5, max=50
        "password": string      min=5, max=50
        "fullName": string      regex=/^[\w_\-\. ]+$/u
    }
    
**Route: /api/users/{id}**

GET
    
    Read a user with id without admin privilege, can only get account with ROLE_USER
    Query Parameters: {
        "id": int               optional
        "fullName": string      optional
    }
    Json Body: null
    Example:
        GET /api/users/1        Get user with id 1
    
    
PUT
    
    Update a user with id without admin privilege. Can only update self if without admin privilege
    Json Body: {
        "password": string      optional, min=5, max=50
        "fullName": string      regex=/^[\w_\-\. ]+$/u
    }
    Example:
        POST /api/users/1               Change name user of id 1 to "Testing"
        Content: {
            "fullName": "Testing"
        }
        
        
**Route: /api/security/login**
    
POST   
    
    Login and start session
    Json Body: {
        "username": string      min=1
        "password": string      min=1
    }