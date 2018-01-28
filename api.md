## Table of contents

- [\Plinker\Core\Server](#class-plinkercoreserver)
- [\Plinker\Core\Client](#class-plinkercoreclient)
- [\Plinker\Core\Endpoint\Test](#class-plinkercoreendpointtest)
- [\Plinker\Core\Lib\Curl](#class-plinkercorelibcurl)
- [\Plinker\Core\Lib\Signer](#class-plinkercorelibsigner)

<hr />

### Class: \Plinker\Core\Server

> Plinker\Core\Server

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>listen()</strong> : <em>string</em><br /><em>Listen method <code> $server->listen(); </code></em> |

<hr />

### Class: \Plinker\Core\Client

> Plinker\Core\Client

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__call(</strong><em>string</em> <strong>$action</strong>, <em>array</em> <strong>$params</strong>)</strong> : <em>array</em><br /><em>Magic caller method, which calls component</em> |
| public | <strong>__construct(</strong><em>string</em> <strong>$server</strong>, <em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>__get(</strong><em>string</em> <strong>$component</strong>)</strong> : <em>object</em><br /><em>Magic getter method, which sets component</em> |

<hr />

### Class: \Plinker\Core\Endpoint\Test

> Plinker\Core\Endpoint\Test

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>config()</strong> : <em>void</em> |
| public | <strong>this()</strong> : <em>void</em> |

<hr />

### Class: \Plinker\Core\Lib\Curl

> Plinker\Core\Lib\Curl

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>post(</strong><em>string</em> <strong>$url</strong>, <em>array</em> <strong>$parameters=array()</strong>, <em>array</em> <strong>$headers=array()</strong>)</strong> : <em>void</em><br /><em>POST</em> |

<hr />

### Class: \Plinker\Core\Lib\Signer

> Plinker\Core\Lib\Signer

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$config=array()</strong>)</strong> : <em>void</em><br /><em>Class construct</em> |
| public | <strong>decode(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>mixed</em><br /><em>Decrypt, verify and unserialize payload.</em> |
| public | <strong>encode(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>array</em><br /><em>Sign and encrypt into payload array.</em> |

