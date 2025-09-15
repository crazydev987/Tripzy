// BookHotelPage.jsx
import React, { useEffect, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { loadStripe } from "@stripe/stripe-js";

const stripePromise = loadStripe("pk_test_your_public_key_here");

const BookHotelPage = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [hotel, setHotel] = useState(null);
  const [loading, setLoading] = useState(true);
  const [passenger, setPassenger] = useState({ name: "", email: "" });
  const [processing, setProcessing] = useState(false);

  const hotelId = searchParams.get("hotelId");
  const checkIn = searchParams.get("checkIn");
  const checkOut = searchParams.get("checkOut");

  useEffect(() => {
    const fetchHotel = async () => {
      try {
        const res = await fetch(
          `http://localhost/backend/hotels_view.php?hotelId=${hotelId}&checkIn=${checkIn}&checkOut=${checkOut}`
        );
        const data = await res.json();
        if (data.hotel) setHotel(data.hotel);
        setLoading(false);
      } catch (err) {
        console.error("Failed to fetch hotel:", err);
        setLoading(false);
      }
    };
    fetchHotel();
  }, [hotelId, checkIn, checkOut]);

  const handleChange = (e) => {
    setPassenger({ ...passenger, [e.target.name]: e.target.value });
  };

  const handlePayment = async () => {
    if (!passenger.name || !passenger.email) {
      alert("Please enter passenger name and email.");
      return;
    }

    setProcessing(true);

    try {
      // Call your backend PHP endpoint to create Stripe session
      const res = await fetch("http://localhost/backend/create_stripe_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: hotel.name,
          price: hotel.price,
          currency: hotel.currency,
          passenger,
        }),
      });

      const session = await res.json();
      if (session.error) {
        alert(session.error);
        setProcessing(false);
        return;
      }

      const stripe = await stripePromise;
      const { error } = await stripe.redirectToCheckout({ sessionId: session.id });

      if (error) {
        alert(error.message);
        setProcessing(false);
      }
    } catch (err) {
      console.error(err);
      alert("Payment failed");
      setProcessing(false);
    }
  };

  // Handle loading state
  if (loading) return <p>Loading hotel details...</p>;
  if (!hotel) return <p>No hotel data found.</p>;

  return (
    <div style={{ maxWidth: "500px", margin: "auto" }}>
      <h2>{hotel.name}</h2>
      <p>City: {hotel.city}</p>
      <p>Room Type: {hotel.roomType}</p>
      <p>
        Price: {hotel.price} {hotel.currency}
      </p>
      <p>Check-In: {hotel.checkIn}</p>
      <p>Check-Out: {hotel.checkOut}</p>

      <h3>Passenger Details</h3>
      <input
        type="text"
        name="name"
        placeholder="Passenger Name"
        value={passenger.name}
        onChange={handleChange}
        style={{ display: "block", marginBottom: "10px", width: "100%" }}
      />
      <input
        type="email"
        name="email"
        placeholder="Passenger Email"
        value={passenger.email}
        onChange={handleChange}
        style={{ display: "block", marginBottom: "10px", width: "100%" }}
      />

      <button onClick={handlePayment} disabled={processing}>
        {processing ? "Processing..." : "Pay with Stripe"}
      </button>
    </div>
  );
};

export default BookHotelPage;
