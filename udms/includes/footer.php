<?php $public=$public??false; if(!$public && me()): ?>
</div></main></div>
<footer class="udms-site-footer"><span>&copy; <?=date('Y')?> St. Clare College of Caloocan. All rights reserved.</span><span>University Document Management System</span><em>Faith. Excellence. Service.</em></footer>
</div><?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/app.js"></script>
<?php if(!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
<script>flashMsg(<?=json_encode($f['t'])?>,<?=json_encode($f['m'])?>);</script><?php endif; ?>
</body></html>
