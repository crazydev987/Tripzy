import React, { useEffect, useState } from "react";
import Navbar from "../components/Navbar";
import "../styles/TransfersPage.css";

export default function TransfersPage() {
  const [transfers, setTransfers] = useState([]);

  useEffect(() => {
    fetch("http://localhost/Tripzy/backend/transfers_view.php")
      .then(res => res.json())
      .then(data => setTransfers(data))
      .catch(err => console.error("Error fetching transfers:", err));
  }, []);

  return (
    <>
      <Navbar />
      <div className="transfers-page">
        <h2>Available Transfers</h2>
        <div className="transfer-list">
          {transfers.length > 0 ? (
            transfers.map((transfer, index) => (
              <div key={index} className="transfer-card">
                <p><strong>{transfer.start_location}</strong> → <strong>{transfer.end_location}</strong></p>
                <p><strong>Vehicle:</strong> {transfer.vehicle_type}</p>
                <p><strong>Passengers:</strong> {transfer.passengers}</p>
                <p><strong>Price:</strong> {transfer.currency} {transfer.price}</p>
                <p><strong>Type:</strong> {transfer.transfer_type}</p>
                <p><strong>Date & Time:</strong> {transfer.date_time}</p>
              </div>
            ))
          ) : (
            <p>No transfers available.</p>
          )}
        </div>
      </div>
    </>
  );
}
