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
<h2>Defining Routes</h2>

<h3>1. Basic Route (GET)</h3>
<pre><code>// Long form
$router->addRoute(['home'], function() {
    echo "Welcome Home";
});

// Shortcut (recommended)
$router->get(['home'], function() {
    echo "Welcome Home";
});</code></pre>

<h3>2. Route with Parameters</h3>
<pre><code>// With single parameter
$router->get(['users', '{id}'], function($params) {
    echo "User ID: " . $params['id'];
});

// With multiple parameters
$router->get(['posts', '{post_id}', 'comments', '{comment_id}'], 
    function($params) {
        // Access params: $params['post_id'], $params['comment_id']
    }
);</code></pre>

<h3>3. HTTP Method-Specific Routes</h3>
<pre><code>// POST route
$router->post(['contact'], 'ContactController@submit');

// PUT route
$router->put(['articles', '{id}'], 'ArticleController@update');

// DELETE route
$router->delete(['articles', '{id}'], 'ArticleController@delete');

// Multiple methods
$router->addRoute(['products'], 'ProductController@update', ['PUT', 'PATCH']);</code></pre>

<h3>4. Route with Middleware</h3>
<pre><code>// Single middleware
$router->get(['admin'], 'AdminController@dashboard', [
    function() {
        if (!user_is_admin()) {
            header('Location: /login');
            exit;
        }
    }
]);

// Multiple middleware
$router->post(['orders'], 'OrderController@create', [
    AuthMiddleware::class,
    CsrfMiddleware::class
]);</code></pre>

<h3>5. RESTful Resource Example</h3>
<pre><code>// User resource routes
$router->get(['users'], 'UserController@index');          // List users
$router->get(['users', '{id}'], 'UserController@show');   // View user
$router->post(['users'], 'UserController@store');         // Create user
$router->put(['users', '{id}'], 'UserController@update'); // Update user
$router->delete(['users', '{id}'], 'UserController@delete'); // Delete user</code></pre>

<h3>Key Notes</h3>
<ul>
  <li>Paths are defined as arrays (e.g., <code>['users', '{id}']</code>)</li>
  <li>Parameters are enclosed in <code>{}</code> (one per segment)</li>
  <li>Shortcut methods (<code>get()</code>, <code>post()</code>, etc.) default to their respective HTTP methods</li>
  <li>Use <code>addRoute()</code> when you need custom HTTP methods</li>
</ul>

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
// Global middleware (runs on all routes)
<pre><code>
  $router->addPrefixes([
  function() { /* CORS headers */ }
]);
 $router->addSuffixes([
  function() { /* CORS headers */ }
]);</code>
</pre>


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
