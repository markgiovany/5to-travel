function handleCredentialResponse(response){

fetch("auth/login_google.php",{
method:"POST",
headers:{
"Content-Type":"application/json"
},
body:JSON.stringify({
token:response.credential
})
})
.then(res=>res.text())
.then(data=>{

window.location="dashboard.php"

})

}