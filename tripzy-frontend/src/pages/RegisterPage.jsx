import React, { useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import "../styles/Auth.css";

export default function RegisterPage() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [message, setMessage] = useState("");

  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const hotelQuery = searchParams.toString(); // preserve hotel params

  const handleRegister = async (e) => {
    e.preventDefault();
    setMessage("");

    try {
      const response = await fetch("http://localhost/webcapstone/Tripzy_New/backend/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password }),
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        // redirect to login with hotel query params
        navigate(`/login?${hotelQuery}`);
      } else {
        setMessage(data.message || "Registration failed");
      }
    } catch (error) {
      console.error("Registration error:", error);
      setMessage("Something went wrong. Please try again.");
    }
  };

  const handleLoginRedirect = () => {
    navigate(`/login?${hotelQuery}`);
  };

  return (
    <div className="auth-page">
      <h2>Register</h2>
      <form onSubmit={handleRegister}>
        <input
          type="text"
          placeholder="Full Name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
        />
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        <button type="submit">Register</button>
      </form>

      {message && <p className="error">{message}</p>}

      <p>
        Already have an account?{" "}
        <span className="link" onClick={handleLoginRedirect}>
          Login
        </span>
      </p>
    </div>
  );
}
