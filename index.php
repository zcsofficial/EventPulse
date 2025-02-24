<?php
// Start session
session_start();

// Include database config (optional for future dynamic content)
require_once 'config.php';

// Redirect logged-in users to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPulse - Modern Event Management Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1A2A44',
                        secondary: '#FF6F61'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1A2A44 0%, #0F1829 100%);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(26, 42, 68, 0.1);
        }
        .pricing-card.highlighted {
            border: 2px solid #FF6F61;
        }
    </style>
</head>
<body class="bg-[#F5F6F5]">
    <nav class="bg-primary fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="font-['Pacifico'] text-2xl text-white">EventPulse</a>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-8">
                            <a href="#features" class="text-white hover:text-[#F8DDA4] px-3 py-2 text-sm font-medium">Features</a>
                            <a href="#how-it-works" class="text-white hover:text-[#F8DDA4] px-3 py-2 text-sm font-medium">How It Works</a>
                            <a href="#pricing" class="text-white hover:text-[#F8DDA4] px-3 py-2 text-sm font-medium">Pricing</a>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-white hover:text-[#F8DDA4] px-3 py-2 text-sm font-medium !rounded-button">Sign In</a>
                    <a href="register.php" class="bg-secondary text-white px-4 py-2 text-sm font-medium hover:bg-opacity-90 !rounded-button">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="gradient-bg pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 text-center md:text-left">
                    <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">Transform Your Events into Unforgettable Experiences</h1>
                    <p class="text-lg text-[#F5F6F5] mb-8">Streamline planning, enhance engagement, and create memorable moments with our comprehensive event management platform.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <a href="register.php" class="bg-secondary text-white px-8 py-4 font-medium hover:bg-opacity-90 !rounded-button whitespace-nowrap">Start Free Trial</a>
                        <button class="border-2 border-[#F8DDA4] text-[#F8DDA4] px-8 py-4 font-medium hover:bg-[#F8DDA4] hover:text-primary !rounded-button whitespace-nowrap">Watch Demo</button>
                    </div>
                </div>
                <div class="md:w-1/2 mt-10 md:mt-0">
                    <img src="https://public.readdy.ai/ai/img_res/6e878c379f54ccfdaa373dcd54ba68d7.jpg" alt="EventPulse Platform" class="rounded-lg shadow-2xl">
                </div>
            </div>
        </div>
    </header>

    <section id="features" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4">Why Choose EventPulse?</h2>
                <p class="text-lg text-gray-600">Everything you need to create and manage successful events</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="feature-card bg-white p-8 rounded-lg shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mb-6">
                        <i class="ri-calendar-line text-secondary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Smart Scheduling</h3>
                    <p class="text-gray-600">AI-powered scheduling that automatically suggests optimal event timings based on attendee preferences.</p>
                </div>
                <div class="feature-card bg-white p-8 rounded-lg shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mb-6">
                        <i class="ri-group-line text-secondary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Attendee Management</h3>
                    <p class="text-gray-600">Comprehensive tools for registration, check-in, and engagement tracking throughout your events.</p>
                </div>
                <div class="feature-card bg-white p-8 rounded-lg shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center mb-6">
                        <i class="ri-bar-chart-line text-secondary text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Analytics & Insights</h3>
                    <p class="text-gray-600">Real-time analytics and post-event reports to measure success and improve future events.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4">How It Works</h2>
                <p class="text-lg text-gray-600">Get started in minutes with our simple three-step process</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div class="w-16 h-16 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-secondary">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Create Your Event</h3>
                    <p class="text-gray-600">Set up your event details, schedule, and customize registration forms.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-secondary">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Manage Registrations</h3>
                    <p class="text-gray-600">Track attendees, send communications, and handle payments securely.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-secondary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-2xl font-bold text-secondary">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-primary mb-4">Execute & Analyze</h3>
                    <p class="text-gray-600">Run your event smoothly and gather insights for future improvement.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-4">Simple, Transparent Pricing</h2>
                <p class="text-lg text-gray-600">Choose the perfect plan for your event management needs</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="pricing-card bg-white p-8 rounded-lg shadow-lg">
                    <h3 class="text-xl font-bold text-primary mb-2">Starter</h3>
                    <div class="text-4xl font-bold text-primary mb-6">₹4,900<span class="text-lg text-gray-500">/month</span></div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Up to 5 events/month</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Basic analytics</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Email support</li>
                    </ul>
                    <a href="register.php" class="w-full block bg-secondary text-white py-3 font-medium hover:bg-opacity-90 !rounded-button text-center">Choose Starter</a>
                </div>
                <div class="pricing-card highlighted bg-white p-8 rounded-lg shadow-lg relative">
                    <div class="absolute top-0 right-0 bg-secondary text-white px-4 py-1 rounded-tr-lg rounded-bl-lg text-sm">Most Popular</div>
                    <h3 class="text-xl font-bold text-primary mb-2">Professional</h3>
                    <div class="text-4xl font-bold text-primary mb-6">₹9,900<span class="text-lg text-gray-500">/month</span></div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Up to 20 events/month</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Advanced analytics</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Priority support</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Custom branding</li>
                    </ul>
                    <a href="register.php" class="w-full block bg-secondary text-white py-3 font-medium hover:bg-opacity-90 !rounded-button text-center">Choose Professional</a>
                </div>
                <div class="pricing-card bg-white p-8 rounded-lg shadow-lg">
                    <h3 class="text-xl font-bold text-primary mb-2">Enterprise</h3>
                    <div class="text-4xl font-bold text-primary mb-6">₹24,900<span class="text-lg text-gray-500">/month</span></div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Unlimited events</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>Full analytics suite</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>24/7 support</li>
                        <li class="flex items-center"><i class="ri-check-line text-[#F8DDA4] mr-2"></i>API access</li>
                    </ul>
                    <a href="register.php" class="w-full block bg-secondary text-white py-3 font-medium hover:bg-opacity-90 !rounded-button text-center">Choose Enterprise</a>
                </div>
            </div>
        </div>
    </section>

    <section class="gradient-bg py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to Transform Your Events?</h2>
            <p class="text-lg text-[#F5F6F5] mb-8">Join thousands of successful event organizers who trust EventPulse</p>
            <a href="register.php" class="bg-secondary text-white px-8 py-4 font-medium hover:bg-opacity-90 !rounded-button">Start Your Free Trial</a>
            <div class="mt-8 flex justify-center space-x-6">
                <img src="https://public.readdy.ai/ai/img_res/58b6cb3aa0c3a89ed3bce3b1a496a18d.jpg" alt="Trust Badge" class="h-10">
                <img src="https://public.readdy.ai/ai/img_res/272f00eabe8e8f763be9ab0d117ce273.jpg" alt="Trust Badge" class="h-10">
                <img src="https://public.readdy.ai/ai/img_res/b90e7b5e5eba9bf4250089bad669120e.jpg" alt="Trust Badge" class="h-10">
            </div>
        </div>
    </section>

    <footer class="bg-primary text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <a href="#" class="font-['Pacifico'] text-2xl text-white mb-4 block">EventPulse</a>
                    <p class="text-[#F5F6F5] text-sm">Making event management simple and powerful for everyone.</p>
                </div>
                <div>
                    <h4 class="text-[#F8DDA4] font-medium mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-[#F5F6F5] hover:text-white">Features</a></li>
                        <li><a href="#pricing" class="text-[#F5F6F5] hover:text-white">Pricing</a></li>
                        <li><a href="#" class="text-[#F5F6F5] hover:text-white">Security</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[#F8DDA4] font-medium mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-[#F5F6F5] hover:text-white">About</a></li>
                        <li><a href="#" class="text-[#F5F6F5] hover:text-white">Blog</a></li>
                        <li><a href="#" class="text-[#F5F6F5] hover:text-white">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-[#F8DDA4] font-medium mb-4">Stay Updated</h4>
                    <form class="flex gap-2">
                        <input type="email" placeholder="Enter your email" class="bg-white/10 text-white placeholder-gray-400 px-4 py-2 rounded-button focus:outline-none focus:ring-2 focus:ring-secondary">
                        <button type="submit" class="bg-secondary text-white px-4 py-2 !rounded-button hover:bg-opacity-90">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="border-t border-white/10 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-[#F5F6F5] text-sm">© <?php echo date('Y'); ?> EventPulse. All rights reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-[#F5F6F5] hover:text-secondary"><i class="ri-twitter-fill text-xl"></i></a>
                    <a href="#" class="text-[#F5F6F5] hover:text-secondary"><i class="ri-linkedin-fill text-xl"></i></a>
                    <a href="#" class="text-[#F5F6F5] hover:text-secondary"><i class="ri-facebook-fill text-xl"></i></a>
                    <a href="#" class="text-[#F5F6F5] hover:text-secondary"><i class="ri-instagram-fill text-xl"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('nav a[href^="#"]');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        });
    </script>
</body>
</html>