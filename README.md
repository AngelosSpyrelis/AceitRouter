<div align="center">
  <h1>AceitRouter</h1>
  <p>A PHP router that supports nested routes and middleware hooks at two points of the matching process.</p>
  
  <div>
    <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php" alt="PHP Version">
    <img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License">
  </div>
</div>

<h2>✨ Features</h2>
<ul>
  <li><strong>Middleware Hooks</strong> (prefixes/suffixes)</li>
  <li><strong>Nested Routes</strong> (e.g., /admin/users/edit)</li>
  <li><strong>Fallback Handlers</strong> (404, defaults, and error pages)</li>
  <li><strong>Callable Validation</strong> (Warns about invalid callbacks)</li>
</ul>

<h2>📦 Installation</h2>

<h3>1. Copy the router file</h3>
<pre><code>cp router/AceitRouter.php ./src/</code></pre>

<h3>2. Include it in your initialization</h3>
<p>I recommend creating an <code>init-router.php</code> file:</p>
<pre><code class="language-php">&lt;?php
// init-router.php
require_once 'src/AceitRouter.php';

// Your setup code here
</code></pre>

<h2>⚡ Quick Start</h2>

<h3>1. Initialize the Router</h3>
<pre><code class="language-php">$router = new AceitRouter();</code></pre>

<h3>2. Set Up Routes</h3>
<pre><code class="language-php">// Set default route
$router->setDefault(fn() => echo "Welcome Home!");

// Add simple route
$router->addRoute('about', fn() => echo "About Us");

// Nested route (matches /api/v1/users)
$router->addRoute('users', fn() => fetchUsers(), ['api', 'v1']);</code></pre>

<h3>3. Add Middleware (optional)</h3>
<pre><code class="language-php">// Prefix middleware (runs before routing)
$router->addPrefixes(function() {
    error_log("Request started: " . $_SERVER['REQUEST_URI']);
});

// Suffix middleware (runs after routing)
$router->addSuffixes([$analytics, $cleanup]);</code></pre>

<h3>4. Handle Requests</h3>
<pre><code class="language-php">$router->handleRequest();</code></pre>

<h2>🛡️ Error Handling</h2>
<pre><code class="language-php">// Custom 404 handler
$router->setFallback(fn() => http_response_code(404));

// Invalid callback handler
$router->setPageErrorDefault(fn() => echo "Route error!");</code></pre>

