<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ওয়াচ শপ - প্রিমিয়াম টাইমপিস</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Hind Siliguri', sans-serif; }
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1523170335258-f5ed11844a49?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3wzNjUyOXwwfDF8c2VhcmNofDR8fHdhdGNofGVufDB8fHx8MTY5OTAxNjA2OXww&ixlib=rb-4.0.3&q=80&w=1600');
            background-size: cover; background-position: center; height: 60vh;
            display: flex; align-items: center; justify-content: center; color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }
        .product-card { opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease-out, transform 0.5s ease-out; }
        .product-card.fade-in { opacity: 1; transform: translateY(0); }
        .product-card img { height: 250px; object-fit: contain; }
        
        .main-price {
            font-size: 0.9rem;
            color: red;
            text-decoration: line-through;
        }
        .discount-price {
            font-size: calc(0.9rem + 5px); /* আসল মূল্য থেকে ৫px বড় */
            color: green;
            font-weight: 600;
        }
        .price-placeholder { /* দামের জায়গা ঠিক রাখার জন্য */
            height: 21px;
            margin-bottom: 0.5rem;
        }
        /* বিবরণীতে লাইন ব্রেক দেখানোর জন্য */
        /* .card-text {
            white-space: pre-wrap;
        } */
    </style>
</head>