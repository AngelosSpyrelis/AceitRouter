<div align="center">
  <h1>AceitRouter</h1>
  <p>A Lightweight PHP Router.</p>
  
  <div>
    <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php" alt="PHP Version">
    <img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License">
  </div>
</div>

<h2>Features</h2>
<ul>
  <li>Case-sensitive/case-insensitive URL matching</li>
  <li>Nested route definitions</li>
  <li>Prefix/suffix middleware</li>
  <li>Route-specific middleware</li>
  <li>Dynamic route parameters (e.g. <code>/users/{id}</code>)</li>
</ul>

<h2>Installation</h2>
<pre><code>composer require aceitdesign/router</code></pre>

<h2>Basic Usage</h2>

<h3>1. Initialize the Router</h3>
<pre><code>&lt;?php
require 'vendor/autoload.php';

$router = new AceitDesign\Router\AceitRouter(
  isCaseSensitive: false // Optional (default: false)
);
</code></pre>
<p>When you create an instance of the router you can choose whether it will convert the entire URI to lowercase before any of the middleware or matching are called. To activate it, you can pass 'true' to the constructor</p>
<h3>2. Define Routes</h3>
<pre><code>// Simple route
$router->addRoute(['home'], function() {
  echo "Home Page";
});

// Route with parameters
$router->addRoute(['users', '{id}'], function($params) {
  echo "User ID: " . $params['id'];
});

// Route with middleware
$router->addRoute(['admin'], 'AdminController@index', [
  function() { /* Auth check */ }
]);
</code></pre>
<h2>Route Definition</h2>

<p>To add routes:</p>

<ol>
  <li>
    <strong>Pass the entire path as an array</strong>:
    <pre><code>['about', 'projects']  // Matches /about/projects</code></pre>
    The router will automatically create nested paths if they don't exist.
  </li>

  <li>
    <strong>Provide a handler callable</strong>:
    <pre><code>$router->addRoute(['about', 'projects'], function() {
    // Your route logic here
});</code></pre>
    Internally, this creates:
    <pre><code>[
    'about' => [
        'projects' => [
            'handler' => function() {...}
        ]
    ]
]</code></pre>
  </li>
</ol>

<h2>Route Parameters</h2>

<p>To add parameters:</p>

<ul>
  <li>Enclose parameter names in curly braces <code>{}</code></li>
  <li>Only one parameter per segment is supported</li>
  <li>Example:
    <pre><code>['users', '{id}']  // Matches /users/42</code></pre>
    Creates:
    <pre><code>[
    'users' => [
        'params' => 'id',
        // ... nested routes
    ]
]</code></pre>
  </li>
</ul>

<h3>Important Notes About Parameters:</h3>

<ol>
  <li>The router collects all parameters until the last available handler</li>
  <li>For <code>['users','{id}','posts','{post_id}']</code>:
    <ul>
      <li>Parameters are passed to the last non-parameter segment's handler</li>
      <li>If only 'users' has a handler, it receives all parameters</li>
    </ul>
  </li>
  <li><strong>Parameter validation is the handler's responsibility</strong></li>
  <li><strong>Best practice</strong>: Use parameters only for required values</li>
</ol>

<h2>Example Workflow</h2>

<pre><code>// Define route with parameters
$router->addRoute(['users', '{id}', 'posts', '{post_id}'], function($params) {
    // $params contains both id and post_id
    echo "User: {$params['id']}, Post: {$params['post_id']}";
});

// Matches:
// /users/42/posts/99 → User: 42, Post: 99
</code></pre>
<h3>3. Configure Fallbacks (Optional)</h3>
<pre><code>// 404 Handler
$router->setFallback(function() {
  http_response_code(404);
  echo "Page not found";
});
<p>You can also set default callbacks for cases where a page is not found or any errors occur.</p>
// Empty route handler
$router->setDefault(function() {
  echo "Home Page";
});
</code></pre>

<h3>4. Add Middleware</h3>
<pre><code>// Global middleware (runs on all routes)
$router->addPrefixes([
  function() { /* CORS headers */ }
]);

<h3>5. Handle Requests</h3>
<pre><code>// In your entry point (e.g. index.php)
$router->handleRequest();
</code></pre>

<h2>Advanced Examples</h2>

<h3>Nested Routes</h3>
<pre><code>$router->addRoute(['api', 'v1', 'users'], 'ApiController@users');
// Matches: /api/v1/users
</code></pre>

<h3>Multiple Parameters</h3>
<pre><code>$router->addRoute(['posts', '{id}', 'comments', '{comment_id}'], 
  function($params) {
    // $params contains both id and comment_id
  }
);
// Matches: /posts/42/comments/99
</code></pre>

<h2>Error Handling</h2>
<p>The router provides three levels of fallback:</p>
<ol>
  <li><code>setDefault()</code> - For empty paths (/)</li>
  <li>Route-specific handlers - For valid routes</li>
  <li><code>setFallback()</code> - For 404 errors</li>
</ol>
