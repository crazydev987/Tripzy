import React from "react";
import { Link, useNavigate } from "react-router-dom";
import "../App.css";

export default function Navbar() {
  const navigate = useNavigate();
  let user = null;

  try {
    const storedUser = localStorage.getItem("user");
    if (storedUser && storedUser !== "undefined") user = JSON.parse(storedUser);
  } catch (err) {
    console.error(err);
  }

  const handleLogout = () => {
    localStorage.removeItem("user");
    navigate("/login");
  };

  return (
    <nav className="navbar">
      <div className="navbar-logo">
        <Link to="/"><img src="/assets/logo.png" alt="Tripzy Logo" /></Link>
      </div>
      <ul className="navbar-links">
        <li><Link to="/hotels" style={{ color: 'white' }}>Hotels</Link></li>
        <li><Link to="/transfers" style={{ color: 'white' }}>Transfers</Link></li>
        {user ? (
          <>
            <li className="navbar-user">Hello, {user.name}</li>
            <li><button className="btn-logout" onClick={handleLogout}>Logout</button></li>
          </>
        ) : (
          <li><Link to="/login" style={{ color: 'white' }}>Login</Link></li>
        )}
      </ul>
    </nav>
  );
}
