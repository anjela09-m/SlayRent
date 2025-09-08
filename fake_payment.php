<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment Demo</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        /* ...styles from previous answer... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(45deg, #3b82f6, #1d4ed8); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 1.1rem; }
        .demo-info { background: #fef3c7; border: 2px solid #f59e0b; border-radius: 10px; padding: 20px; margin: 30px 40px; text-align: center; }
        .demo-info h3 { color: #92400e; margin-bottom: 10px; }
        .demo-info p { color: #78350f; line-height: 1.6; }
        .test-cards { background: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 10px; padding: 20px; margin: 20px 40px; }
        .test-cards h3 { color: #0c4a6e; margin-bottom: 15px; text-align: center; }
        .card-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .card-item { background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #0ea5e9; }
        .card-item strong { color: #0c4a6e; display: block; margin-bottom: 5px; }
        .loading { display: none; text-align: center; padding: 20px; }
        .spinner { border: 4px solid #f3f4f6; border-top: 4px solid #3b82f6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .status-message { padding: 15px; border-radius: 10px; margin: 20px 40px; text-align: center; display: none; }
        .success { background: #d1fae5; border: 2px solid #10b981; color: #065f46; }
        .error { background: #fee2e2; border: 2px solid #ef4444; color: #7f1d1d; }
        .pay-btn { background: linear-gradient(45deg, #10b981, #059669); color: white; border: none; padding: 14px 32px; border-radius: 25px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; margin: 30px auto 0; display: block; }
        .pay-btn:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ SlayRent Payment Demo</h1>
            <p>Razorpay Payment Integration Demo</p>
        </div>
        <div class="demo-info">
            <h3>⚠️ Demo Information</h3>
            <p>This is a demo version of the Razorpay payment integration. You can try out the payment process without making any real transactions.</p>
        </div>
        <div class="test-cards">
            <h3>💳 Test Cards</h3>
            <div class="card-info">
                <div class="card-item"><strong>Visa Card</strong>4111 1111 1111 1111</div>
                <div class="card-item"><strong>MasterCard</strong>5500 0000 0000 0004</div>
                <div class="card-item"><strong>Razorpay Card</strong>3782 8224 6310 005</div>
                <div class="card-item"><strong>JCB Card</strong>3530 1113 0200 0000</div>
            </div>
        </div>
        <div class="loading" id="loading">
            <div class="spinner"></div>
            Processing Payment...
        </div>
        <div class="status-message success" id="success-message">
            Payment Successful! 🎉
        </div>
        <div class="status-message error" id="error-message">
            Payment Failed! Please try again. ❌
        </div>
        <button class="pay-btn" onclick="initiatePayment()">Proceed to Pay</button>
    </div>
    <script>
        function initiatePayment() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('success-message').style.display = 'none';
            document.getElementById('error-message').style.display = 'none';
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
                const options = {
                    key: "rzp_test_RDRydETJkRioj4",
                    amount: 50000, // Amount in paise
                    currency: "INR",
                    name: "SlayRent",
                    description: "Test Payment",
                    image: "https://example.com/logo.png",
                    handler: function (response) {
                        document.getElementById('success-message').style.display = 'block';
                        document.getElementById('error-message').style.display = 'none';
                        console.log(response);
                    },
                    prefill: {
                        name: "John Doe",
                        email: "john.doe@example.com",
                        contact: "9999999999"
                    },
                    notes: {
                        address: "note value"
                    },
                    theme: {
                        color: "#3399cc"
                    },
                    modal: {
                        ondismiss: function() {
                            document.getElementById('error-message').style.display = 'block';
                            document.getElementById('success-message').style.display = 'none';
                        }
                    }
                };
                const rzp1 = new Razorpay(options);
                rzp1.on('payment.failed', function () {
                    document.getElementById('error-message').style.display = 'block';
                    document.getElementById('success-message').style.display = 'none';
                });
                rzp1.open();
            }, 1000);
        }
    </script>
</body>
</html>