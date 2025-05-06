let isLogin = true;

function toggleForm() {
    isLogin = !isLogin;
    document.getElementById("form-title").innerText = isLogin ? "Login" : "Sign Up";
    document.getElementById("toggle-form").innerHTML = isLogin ?
        "Don't have an account? <span onclick='toggleForm()'>Sign Up</span>" :
        "Already have an account? <span onclick='toggleForm()'>Login</span>";
}

function submitForm() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    
    if (email === "" || password === "") {
        alert("Please fill in all fields.");
    } else {
        alert(isLogin ? "Logging in..." : "Signing up...");
    }
}
