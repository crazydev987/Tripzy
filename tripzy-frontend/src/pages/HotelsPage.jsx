import React, { useEffect, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";

export default function HotelsPage() {
  const [hotels, setHotels] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const city = searchParams.get("city")?.toUpperCase();
  const checkIn = searchParams.get("checkIn");
  const checkOut = searchParams.get("checkOut");

  const user = JSON.parse(localStorage.getItem("user") || "null");

  useEffect(() => {
    const fetchHotels = async () => {
      try {
        const res = await fetch(
          `http://localhost/webcapstone/Tripzy_New/backend/hotels_view.php?city=${city}&checkIn=${checkIn}&checkOut=${checkOut}`
        );
        const data = await res.json();
        setHotels(data.hotels || []);
      } catch {
        setError("Failed to fetch hotels");
      } finally {
        setLoading(false);
      }
    };
    fetchHotels();
  }, [city, checkIn, checkOut]);

  if (loading) return <p>Loading hotels...</p>;
  if (error) return <p>{error}</p>;
  if (!hotels.length) return <p>No hotels found.</p>;

  return (
    <div>
      <h2>Hotels in {city}</h2>
      {hotels.map((hotel) => (
        <div className="hotel-card" key={hotel.hotelId}>
          <div>
            <h3>{hotel.name}</h3>
            <p>Room: {hotel.roomType}</p>
            <p>Price: {hotel.price} {hotel.currency}</p>
          </div>
          <div>
            {user ? (
              <button onClick={() =>
                navigate(`/book-hotel/${hotel.hotelId}?city=${city}&checkIn=${checkIn}&checkOut=${checkOut}`)
              }>Book Now</button>
            ) : (
              <button onClick={() =>
                navigate(`/login?redirect=book&hotelId=${hotel.hotelId}&city=${city}&checkIn=${checkIn}&checkOut=${checkOut}`)
              }>Book Now</button>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
