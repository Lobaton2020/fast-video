<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
</head>
</head>

<body>
    <h2 class="text-center mt-2">Reproduce app</h2>
    <div class="container mt-3 mb-3">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">

            <div id="app" class="ratio ratio-16x9"></div>
                <div class="form-group mt-3">
                    <hr/>
                    <span>Url del video</span>
                    <input class="form-control mt-2" placeholder="Primero poner el titulo" id="name"/>
                    <input class="form-control mt-2" placeholder="Pon url del video a cargar" id="url"/>
                    <button id="button" class="btn btn-success mt-2">Reproducir link</button>
                    <hr/>
                    <span>Velocidad de reproduccion</span>
                    <select class="form-control" id="velocity"></select>
                </div>
                <hr/>
                <h5 class="mt-4 text-center">Listado de links guardados</h5>
                <ul id="list-links" class="d-flex my-3 list-group">
                    <span class="text-center">Opps!! No hay nada</span>
                </ul>
            </div>
            <div class="col-md-1"></div>
        </div>
    </div>
    <style>
        #app{
            background: #ececec;
            box-shadow: 0px 0px 10px gray;
        }
        </style>
    <script>
        const IS_PRODUCTION = true;
        const url = document.querySelector("#url")
        const name = document.querySelector("#name")
        const button = document.querySelector("#button")
        const logger = {
            info:(message)=>console.log(`[INFO][${message}]`),
            error:(message)=>console.log(`[ERROR][${message}]`),
            debug:(message)=>{
                if(!IS_PRODUCTION){
                    console.log(`[DEBUG][${typeof(message) == "object" ? JSON.stringify(message): message}]`)
                }
            },
        }
        const points = ()=>{
            const data = []

            for(let i = 0;i<5000; i+=250){
                let number = i.toString()
                if (i < 1000){
                    number = "0"+number;
                }
                if(i > 1000){
                    number = number.substring(0,3)
                }
                number = `${number.substr(0,1)}.${number.substr(1,2)}`
                data.push(`<option value="${number}">${number}</value>`)
            }
            return data
        }
        points().forEach(item=>{
            document.querySelector("#velocity").innerHTML += item
        })
        const loadUrls = (name)=>{
            try{
                logger.info("Load urls")
                const data = JSON.parse(localStorage.getItem(name))
                logger.debug(data)
                return data;
            }catch(error){
                logger.error(error)
                return []
            }
        };
        const saveUrls = (name,urls)=>{
            try{
                logger.info("Save urls")
                localStorage.setItem(name,JSON.stringify(urls))
                const data = JSON.parse(localStorage.getItem(name))
                logger.debug(data)
                return data;
            }catch(error){
                logger.error(error)
                return []
            }
        };
        const state = {
            urls: loadUrls("URLS-VIDEO")
        }
        const observerChangeVelocity = (video)=>{
            const input = document.querySelector("#velocity")
            input.addEventListener("change",()=>{
                logger.debug(parseFloat(input.value))
                video.playbackRate = parseFloat(input.value);
            })
        }
        const loadVideoImage = (url)=>{
            const app = document.querySelector("#app")
            app.innerHTML=""
            const video = document.createElement("video")
            video.src = url;
            video.play();
            video.playbackRate = 1.25;
            video.controls = "enable"
            app.appendChild(video)
            observerChangeVelocity(video)
        };

        const addVideo = (e)=>{
            if(!url.value) return;
            loadVideoImage(url.value)
            if(!state.urls.find(({url:u})=>u==url.value)){
                state.urls.push({ url: url.value, name: name.value})
            }else{
                state.urls = state.urls.map((item)=>{
                    if(item.url==url.value){
                        return Object.assign(item,{name:name.value})
                    }
                    return item
                })
            }
            saveUrls("URLS-VIDEO", state.urls)
            localStorage.setItem("LAST-URL-VIDEO",JSON.stringify({name:name.value,url:url.value}))
            renderLinks()
        };
        const reproduceNewVideo = (e)=>{
            e.preventDefault()
            const data = JSON.parse(e.target.dataset.info);
            logger.debug(data)
            url.value = data.url
            name.value = data.name || ""
            loadVideoImage(data.url)
        }
        const deleteVideo = (e)=>{
            e.preventDefault()
            const data = JSON.parse(e.target.dataset.delete);
            logger.debug(data)
            state.urls = state.urls.filter(({ url }) => url !== data.url) || []
            saveUrls("URLS-VIDEO", state.urls)
            renderLinks()
        };
        const showListLinks = (data)=>{
            let { url, name } = data
            if(!url) return false;
            if(!name) name = url.substring(0,70)
            const li = document.createElement("li")
            const a = document.createElement("a")
            const aDelete = document.createElement("a")
            li.className = "list-group-item"
            aDelete.innerText = "Delete"
            aDelete.className = "btn btn-danger mx-3"
            a.href = url
            a.innerText = name
            a.className = " ml-3"
            a.dataset.info = JSON.stringify(data)
            aDelete.dataset.delete = JSON.stringify(data)
            aDelete.addEventListener("click",deleteVideo)
            a.addEventListener("click",reproduceNewVideo)
            li.appendChild(aDelete)
            li.appendChild(a)
            return li
        }
        const renderLinks = ()=>{
            const elem = document.querySelector("#list-links")
            const links =state.urls
            .map(showListLinks)
            .filter((item)=> !! item)
            if(links.length == 0){
                elem.innerHTML = '<span class="text-center">Opps!! No hay nada</span>'
            }
            links.map((li,i)=>{
                if(i === 0) elem.innerHTML = "";
                elem.appendChild(li)
            })
        }
        document.addEventListener("DOMContentLoaded",()=>{
            button.addEventListener("click", addVideo);
            renderLinks()
            const lastVideo = JSON.parse(localStorage.getItem("LAST-URL-VIDEO"));
            loadVideoImage(lastVideo.url)
            url.value = lastVideo.url
            name.value = lastVideo.name
        });

    </script>
</body>
</html>