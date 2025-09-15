import React, { useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import "../App.css";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(null);
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const handleLogin = (e) => {
    e.preventDefault();

    if (!email || !password) {
      setError("Please enter both email and password.");
      return;
    }

    localStorage.setItem(
      "user",
      JSON.stringify({ id: Date.now(), name: email.split("@")[0], email })
    );

    // redirect logic
    const redirect = searchParams.get("redirect");
    const hotelId = searchParams.get("hotelId");
    const city = searchParams.get("city");
    const checkIn = searchParams.get("checkIn");
    const checkOut = searchParams.get("checkOut");

    if (redirect === "book" && hotelId) {
      navigate(
        `/book-hotel/${hotelId}?city=${city}&checkIn=${checkIn}&checkOut=${checkOut}`
      );
    } else {
      navigate("/");
    }
  };

  return (
    <div className="login-page">
      <h2>Login</h2>
      <form onSubmit={handleLogin}>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />
        {error && <p className="error">{error}</p>}
        <button type="submit">Login</button>
      </form>
    </div>
  );
}
