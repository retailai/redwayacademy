<h1>Login</h1>
<p>Log in with your phone number — student, parent, or teacher.</p>
<form method="post" action="/login">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Phone number <input name="phone" type="tel" required placeholder="01XXXXXXXXX"></label>
  <label>Password <input name="password" type="password" required></label>
  <button class="btn" type="submit">Login</button>
</form>
<p>No account yet? <a href="/register">Register</a></p>
<p><small>Too many wrong attempts locks login for 15 minutes (5 attempts).</small></p>
