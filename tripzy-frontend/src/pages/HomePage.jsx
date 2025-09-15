import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/HomePage.css";
import Navbar from "../components/Navbar";

export default function HomePage() {
  const [activeTab, setActiveTab] = useState("flights");
  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");
  const [checkIn, setCheckIn] = useState("");
  const [checkOut, setCheckOut] = useState("");
  const [cityError, setCityError] = useState("");

  const navigate = useNavigate();

  const cityMap = {
    London: "LON",
    "New York": "NYC",
    Paris: "PAR",
    Tokyo: "TYO",
    "Los Angeles": "LAX",
  };

  const validateCity = async (city) => {
  try {
    const response = await fetch(
      `http://localhost/webcapstone/Tripzy_New/backend/validate_city.php?city=${encodeURIComponent(city)}`
    );
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("City validation error:", error);
    return null;
  }
};

  const handleSearch = async () => {
    if (activeTab === "hotels") {
      const isValid = await validateCity(to);
      if (!isValid) {
        setCityError("Invalid city. Please enter a valid city.");
        return;
      }
      setCityError("");

      const cityCode = cityMap[to] || to.toUpperCase(); // fallback
      navigate(
        `/hotels?city=${encodeURIComponent(cityCode)}&checkIn=${checkIn}&checkOut=${checkOut}`
      );
    } else if (activeTab === "flights") {
      navigate(
        `/flights?from=${encodeURIComponent(
          from
        )}&to=${encodeURIComponent(to)}&departure=${checkIn}&return=${checkOut}`
      );
    } else if (activeTab === "transfers") {
      navigate(
        `/transfers?pickup=${encodeURIComponent(
          from
        )}&drop=${encodeURIComponent(to)}&date=${checkIn}`
      );
    }
  };

  return (
    <div>
      <Navbar /> {/* Use the extracted navbar */}

      <div className="content">
        <h1>Welcome to Tripzy</h1>
        <p>Your one-stop solution for Flights, Hotels & Transfers</p>

        <div className="search-card">
          <div className="tabs">
            <button
              className={activeTab === "flights" ? "active" : ""}
              onClick={() => setActiveTab("flights")}
            >
              Flights
            </button>
            <button
              className={activeTab === "hotels" ? "active" : ""}
              onClick={() => setActiveTab("hotels")}
            >
              Hotels
            </button>
            <button
              className={activeTab === "transfers" ? "active" : ""}
              onClick={() => setActiveTab("transfers")}
            >
              Transfers
            </button>
          </div>

          {activeTab === "flights" && (
            <div className="tab-content">
              <div className="form-group">
                <input type="text" placeholder="From" value={from} onChange={e => setFrom(e.target.value)} />
                <input type="text" placeholder="To" value={to} onChange={e => setTo(e.target.value)} />
              </div>
              <div className="form-group">
                <input type="date" placeholder="Departure" value={checkIn} onChange={e => setCheckIn(e.target.value)} />
                <input type="date" placeholder="Return" value={checkOut} onChange={e => setCheckOut(e.target.value)} />
              </div>
            </div>
          )}

          {activeTab === "hotels" && (
            <div className="tab-content">
              <div className="form-group">
                <input
                  type="text"
                  placeholder="City (London, New York...)"
                  value={to}
                  onChange={(e) => setTo(e.target.value)}
                />
                {cityError && <p style={{ color: "red" }}>{cityError}</p>}
              </div>
              <div className="form-group">
                <input type="date" placeholder="Check-in" value={checkIn} onChange={e => setCheckIn(e.target.value)} />
                <input type="date" placeholder="Check-out" value={checkOut} onChange={e => setCheckOut(e.target.value)} />
              </div>
            </div>
          )}

          {activeTab === "transfers" && (
            <div className="tab-content">
              <div className="form-group">
                <input type="text" placeholder="Pickup Location" value={from} onChange={e => setFrom(e.target.value)} />
                <input type="text" placeholder="Drop Location" value={to} onChange={e => setTo(e.target.value)} />
              </div>
              <div className="form-group">
                <input type="date" placeholder="Pickup Date" value={checkIn} onChange={e => setCheckIn(e.target.value)} />
              </div>
            </div>
          )}

          <button className="search-btn" onClick={handleSearch}>
            Search
          </button>
        </div>
      </div>
    </div>
  );
}
