// A API privada e as paginas autenticadas NUNCA entram no cache offline.
const CACHE='workflow-assets-v1';
const APP='/WorkFlow/app/';
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll([APP+'icon-192.png',APP+'icon-512.png',APP+'manifest.webmanifest'])));self.skipWaiting();});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))));self.clients.claim();});
self.addEventListener('fetch',e=>{
  const url=new URL(e.request.url);
  if (e.request.method!=='GET' || url.origin!==self.location.origin || !url.pathname.startsWith(APP)) return;
  if (/\.(?:js|css|png|svg|webmanifest)$/.test(url.pathname)) {
    e.respondWith(caches.match(e.request).then(hit=>hit || fetch(e.request).then(res=>{
      if(res.ok) {const clone=res.clone(); caches.open(CACHE).then(c=>c.put(e.request,clone));}return res;
    })));
  }
});
