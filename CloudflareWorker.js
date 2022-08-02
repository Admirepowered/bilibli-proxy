addEventListener("fetch", (event) => {
  event.respondWith(
    handleRequest(event.request).catch(
      (err) => new Response(err.stack, { status: 500 })
    )
  );
});

/**
 * Many more examples available at:
 *   https://developers.cloudflare.com/workers/examples
 * @param {Request} request
 * @returns {Promise<Response>}
 */
async function handleRequest(request) {
  const { pathname } = new URL(request.url);
    var url="https://api.bilibili.com"+pathname
    var a =await fetch(url);
    //var url ="https://api.bilibili.com/pgc/player/web/playurl"
    //return a;
    //var text = a.text();
    //var oReq = new XMLHttpRequest();
    //oReq.open("GET", url);
    //oReq.send();

    return new Response(a.body, {
        headers: { "Content-Type": "application/json",
          "access-control-allow-origin":"*",
          "access-control-allow-headers":"*",
          "cache-control":"no-cache",
          "cross-origin-resource-policy":"cross-origin",
          "access-control-allow-credentials":"true",

        }
      }
      );
       

}
