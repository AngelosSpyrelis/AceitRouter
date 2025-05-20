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

<h2>Defining Routes</h2>

<h3>1. Basic Route Structure</h3>
<pre><code>/**
 * Routes are defined as arrays of path segments.
 * Example: '/about/team' becomes ['about', 'team']
 */
$router->addRoute(['about', 'team'], function() {
    echo "Our Team Page";
});</code></pre>

<h3>2. Route Handlers</h3>
<pre><code>/**
 * The handler is stored under the last path segment.
 * For ['about', 'team'], the structure becomes:
 * [
 *   'about' => [
 *     'team' => [
 *       'handler' => yourCallable
 *     ]
 *   ]
 * ]
 */
$router->get(['products'], 'ProductController@index');</code></pre>

<h3>3. Route Parameters</h3>
<pre><code>/**
 * Parameters are enclosed in {} and stored in the 'params' key.
 * Only one parameter per segment is supported.
 * Example: ['users', '{id}'] stores 'id' in the 'users' segment.
 */
$router->get(['users', '{id}'], function($params) {
    echo "User ID: " . htmlspecialchars($params['id']);
});

/**
 * Important: The router collects all parameters until the last handler.
 * For ['users','{id}','posts','{post_id}'], all params are passed
 * to the final handler.
 */</code></pre>

<h3>4. HTTP Method Handling</h3>
<pre><code>/**
 * Methods are stored with each route definition.
 * Defaults to ['GET'] if not specified.
 */
$router->addRoute(['contact'], 'FormController@submit', ['POST']);

// Shortcut methods automatically set the HTTP method:
$router->post(['login'], 'AuthController@login');  // Sets ['POST']
$router->put(['profile'], 'UserController@update'); // Sets ['PUT']</code></pre>

<h3>5. Middleware Integration</h3>
<pre><code>/**
 * Middleware is stored with its route and executed before the handler.
 * Global middleware should use addPrefixes() instead.
 */
$router->get(['admin'], 'AdminController@index', [
    function() {
        if (!is_admin()) {
            header('Location: /login');
            exit;
        }
    }
]);</code></pre>

<h3>Complete Example</h3>
<pre><code>/**
 * RESTful user resource with parameters and middleware
 */
$router->get(['users'], 'UserController@index'); // GET /users
$router->post(['users'], 'UserController@store', [AuthMiddleware::class]); // POST /users
$router->get(['users', '{id}'], function($params) {
    // $params contains ['id' => value]
}); // GET /users/42
$router->put(['users', '{id}'], 'UserController@update'); // PUT /users/42
$router->delete(['users', '{id}'], 'UserController@delete'); // DELETE /users/42</code></pre>

<h3>Implementation Notes</h3>
<ul>
  <li><strong>Path Segments</strong>: Always use arrays, e.g., <code>['path', 'to', 'route']</code></li>
  <li><strong>Parameters</strong>: Must be enclosed in <code>{}</code> (one per segment)</li>
  <li><strong>Handlers</strong>: Receive parameters as an associative array</li>
  <li><strong>Shortcuts</strong>: <code>get()</code>, <code>post()</code>, etc. are preferred over <code>addRoute()</code></li>
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
