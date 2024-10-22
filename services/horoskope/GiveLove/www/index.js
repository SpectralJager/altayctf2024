const express = require('express');
const couch = require('./database');
const cookieParser = require('cookie-parser');
const multer = require('multer');
const fs = require('fs');
const path = require('path');
const querystring = require('querystring');

const PORT = 3000;
const { createServer } = require('node:http');
const { Server } = require('socket.io');

const app = express();
const server = createServer(app);
const io = new Server(server);

const storageConfig = multer.diskStorage({
    destination: (req, file, callback) => {
        callback(null, "./public/avatars");
    },
    filename: (req, file, callback) => {
        callback(null, Date.now() + file.originalname);
    }
});

app.set('view engine', 'ejs');
app.use(express.urlencoded({ extended: false }));
app.use('/assets', express.static(path.join(__dirname, 'public')));
app.use(cookieParser('secret'));


io.on('connection', (socket) => {
    console.log('A user connected');
    let cookieString = socket.request.headers.cookie
    let user2 =  socket.request.headers.referer
    const startIndex = cookieString.indexOf('token=')+6;
    const endIndex = startIndex + 32
    const startIndex2 = user2.indexOf('Co')+2;
    
    if (startIndex === 0) {
        return null;  // Вернем null если формат строки некорректный
    }
    couch.GetUserbySession(cookieString.substring(startIndex, endIndex), (user)=>{
            let room = user.login + '_' + querystring.unescape((user2.substring(startIndex2, user2.length))).split('').map(char => String.fromCharCode(char.charCodeAt(0) - 16)).join('')
            socket.join(room)
            console.log(room)
            socket.on('disconnect', () => {
                console.log('A user disconnected');
                socket.leave(room)
            });
    })
})


app.get('/', (req, res) => { //------------------------------GET------------------------------------
    res.render('authorization')

})
app.get('/registration', (req, res) => {
    res.render('registration')
})
app.get('/profile', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetUserbySession(req.cookies.token, (user) => {
                res.render('profile', user)
            })
        } else
            res.redirect('/')
    })
})
app.get('/users/:id', (req, res) => {
    let token = req.cookies.token
    couch.OpenSession(token, (b) => {
        if (b) {
            couch.GetUserbySession(req.cookies.token, (us) => {
                if (us.role != 'user') {
                    couch.GetUser(req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) - 16)).join(''), (user) => {
                        res.render('user', user)
                    })
                } else {
                    res.redirect('/refused')
                }
            })
        } else
            res.redirect('/')
    })
})
app.get('/refused', (req, res) => {
    res.render('refused')
})
app.get('/info', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        res.render('info', { b })
    })
})
app.get('/users', (req, res) => {
    const page = parseInt(req.query.page) || 1; // Текущая страница, по умолчанию 1
    const limit = 10; // Количество пользователей на странице
    const startIndex = (page - 1) * limit;
    const endIndex = page * limit;

    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetAllUsers((data) => {
                let users = { users: data.rows };

                // Кодирование идентификаторов пользователей
                users.users.forEach(user => {
                    user.id = user.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('');
                });

                // Получаем нужную страницу
                const paginatedUsers = users.users.slice(startIndex, endIndex);

                // Формируем данные для рендеринга
                res.render('users', {
                    users: paginatedUsers,
                    currentPage: page,
                    totalPages: Math.ceil(users.users.length / limit)
                });
            });
        } else {
            res.redirect('/');
        }
    });
});
app.get('/aries', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('aries', (news) => {
                res.render('aries', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/virgo', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('virgo', (news) => {
                res.render('virgo', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/taurus', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('taurus', (news) => {
                res.render('taurus', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/scorpio', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('scorpio', (news) => {
                res.render('scorpio', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/pisces', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('pisces', (news) => {
                res.render('pisces', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/libra', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('libra', (news) => {
                res.render('libra', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/leo', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('leo', (news) => {
                res.render('leo', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/canser', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('canser', (news) => {
                res.render('canser', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/sagittarius', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('sagittarius', (news) => {
                res.render('sagittarius', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/capricorn', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('capricorn', (news) => {
                res.render('capricorn', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/gemini', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('gemini', (news) => {
                res.render('gemini', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/aquarius', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetNews('aquarius', (news) => {
                res.render('aquarius', news)
            })
        } else
            res.redirect('/')
    })
})
app.get('/horoscope', (req, res) => {
    let token = req.cookies.token
    couch.OpenSession(token, (b) => {
        if (b) {
            couch.GetUserbySession(token, (user) => {
                res.redirect(`/${user.ZZ}`)
            })
        } else
            res.redirect('/')
    })
})

app.get('/messages/:id', (req, res) => {
    couch.OpenSession(req.cookies.token, (b) => {
        if (b) {
            couch.GetUser(req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) - 16)).join(''), (user2)=>{
                if(user2 != false){
                    couch.GetUserbySession(req.cookies.token, (user1)=>{
                        couch.CreateMessage(user1, user2, callback=>{
                            let u = [user1.login, user2.login]
                            u.sort()
                            let nameDB = u[0] +  '_' + u[1] + '_' + "messages"
                            couch.GetMessages(nameDB.toLowerCase(), (data) =>{
                                let users = [3]
                                users[0] = user1
                                users[1] = user2
                                users[1]._id = users[1]._id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')
                                users[2] = data.rows
                                res.render('messages', {title:"", users:users})
                            })
                        })
                    })
                } else{
                    res.redirect(`${('<gl3_' + req.params.id).split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
                }
            })
        } else
            res.redirect('/')
    })
})

app.get('/messages/cript/:id', (req, res) => {
    let id = req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')
    console.log(id)
    console.log(id.split('').map(char => String.fromCharCode(char.charCodeAt(0) - 16)).join(''))
})

app.post('/autch_check', (req, res) => { /*--------------------------------------POST-----------------------------------*/
console.log('<gl3_' + req.body.login.toLowerCase())
    couch.GetUser('<gl3_' + req.body.login.toLowerCase(), (data) => {
        if (req.body.login == data.login && req.body.password == data.password) {
            couch.GenerateSession('<gl3_' + req.body.login.toLowerCase(), (key) => {
                res.cookie('token', key);
                res.cookie('err', 'no');
                res.redirect('/profile')
            })
        } else {
            res.cookie('err', 'logorpass')
            res.redirect('/')
        }
    }, (e) => {
        res.cookie('err', 'logorpass')
        res.redirect('/')
    })
})

app.post('/reg_check', (req, res) => {
    couch.NewUser(req.body, (b) => {
        if (b)
            res.redirect('/')
        else
            res.redirect('/registration')
    })
})

app.post('/profile/delete', (req, res) => {
    couch.GetUserbySession(req.cookies.token, (user) => {
        if (user.login == req.body.login && user.password == req.body.password) {
            couch.DeleteUser(user, (b) => {
                if (b) {
                    res.cookie('token', '')
                    res.cookie('err', 'no')
                    res.redirect('/')
                } else {
                    res.cookie('err', 'errdel')
                    res.redirect('/profile')
                }
            })
        } else {
            res.cookie('err', 'errdel')
            res.redirect('/profile')
        }
    })
})
app.post('/profile/avatar', multer({ storage: storageConfig }).single('file'), (req, res) => {
    couch.GetUserbySession(req.cookies.token, (user) => {
        if (user.avatar != "default.jpg")
            fs.unlinkSync('./public/avatars/' + user.avatar)
        couch.AvatarUpdate(user, req.file.filename, (b) => {
            res.redirect('/profile')

        })
    })
})
app.post('/users/:id/avatar', multer({ storage: storageConfig }).single('file'), (req, res) => {
    couch.GetUser(req.params.id, (user) => {
        if (user.avatar != "default.jpg")
            fs.unlinkSync('./public/avatars/' + user.avatar)
        couch.AvatarUpdate(user, req.file.filename, (b) => {
            res.redirect(`/users/${req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
        })
    })
})
app.post('/users/:id/delete', (req, res) => {
    couch.GetUserbySession(req.cookies.token, (user) => {
        if (user.login == req.body.login && user.password == req.body.password) {
            couch.GetUser(req.params.id, (user) => {
                couch.DeleteUser(user, (b) => {
                    if (b) {
                        res.cookie('err', 'no')
                        res.redirect('/users')
                    } else {
                        console.log("Не получилось")
                        res.cookie('err', 'errdel')
                        res.redirect(`/users/${req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
                    }
                })
            })

        } else {
            console.log("Неверные данные")
            res.cookie('err', 'errdel')
            res.redirect(`/users/${req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
        }
    })
})

app.post('/users/:id/newpass', (req, res) => {
    couch.GetUserbySession(req.cookies.token, (user) => {
        if (user.login == req.body.login && user.password == req.body.pass && req.body.newpass == req.body.newpassrepeat) {
            couch.GetUser(req.params.id, (u) => {
                couch.UpdatePassword(u, req.body.newpass, (b) => {
                    if (b) {
                        res.redirect(`${req.get('Referer')}`)
                    } else {
                        res.redirect(`${req.get('Referer')}`)
                    }
                })
            })
        } else {
            res.cookie('err', 'errpass')
            res.redirect(`${req.get('Referer')}`)

        }
    })
})

app.post('/users/:id/userupdate', (req, res) => {
    couch.GetUser(req.params.id, (user) => {
        couch.AlienUserUpdate(user, req.body, (b) => {
            res.redirect(`/users/${req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
        })
    })
})


app.post('/exit', (req, res) => {
    couch.DeleteSession(req.cookies.token, (b) => {

        res.cookie('token', '')
        res.redirect('/')
    })
})

app.post('/profile/userupdate', (req, res) => {
    couch.GetUserbySession(req.cookies.token, (user) => {
        couch.UserUpdate(user, req.body, (b) => {
            if (b)
                res.redirect('/profile')
        })
    })
})

app.post('/message/:id', (req, res) =>{
    couch.GetUserbySession(req.cookies.token, (user1)=>{
        couch.GetUser(req.params.id.split('').map(char => String.fromCharCode(char.charCodeAt(0) - 16)).join(''), (user2)=>{
            if(user2 != false){
                let u = [user1.login, user2.login]
                u.sort()
                let nameDB = u[0] +  '_' + u[1] + '_' + "messages"
                nameDB = nameDB.toLowerCase()
                var currentdate = new Date()
                        let time = ''
                        if(currentdate.getHours()<10){
                            time += '0' + currentdate.getHours() + ':'
                        } else{
                            time += currentdate.getHours() + ':'
                        }
                        if(currentdate.getMinutes()<10){
                            time+= '0' + currentdate.getMinutes() + ':'
                        } else{
                            time+= currentdate.getMinutes() + ':'
                        }
                        if(currentdate.getSeconds()<10){
                            time+= '0' + currentdate.getSeconds()
                        } else{
                            time+= currentdate.getSeconds()
                        }
                        let date = ''
                        if(currentdate.getDate()<10){
                            date += '0' + currentdate.getDate() + ':'
                        } else{
                            date += currentdate.getDate() + ':'
                        }
                        if(parseInt(currentdate.getMonth() + 1)<10){
                            date += '0' + parseInt(currentdate.getMonth() + 1) + ':'
                        } else{
                            date += parseInt(currentdate.getMonth() + 1) + ':'
                        }
                        date += currentdate.getFullYear()
                let mess = {
                    user: user1,
                    message: req.body.text,
                    time: time,
                    date: date
    
                }
                couch.NewMessage(nameDB, mess, (b)=>{
                    io.to(user2.login + '_' + user1.login).emit('newMessage', mess)
                    console.log(user2.login + '_' + user1.login)
                    res.redirect('/messages/' + req.params.id)
                })
            } else{
                couch.GetUser(('<gl3_' + req.params.id).split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('').split('').map(char => String.fromCharCode(char.charCodeAt(0) - 16)).join(''), (user2)=>{
                        let u = [user1.login, user2.login]
                        u.sort()
                        let nameDB = u[0] +  '_' + u[1] + '_' + "messages"
                        nameDB = nameDB.toLowerCase()
                        var currentdate = new Date()
                                let time = ''
                                if(currentdate.getHours()<10){
                                    time += '0' + currentdate.getHours() + ':'
                                } else{
                                    time += currentdate.getHours() + ':'
                                }
                                if(currentdate.getMinutes()<10){
                                    time+= '0' + currentdate.getMinutes() + ':'
                                } else{
                                    time+= currentdate.getMinutes() + ':'
                                }
                                if(currentdate.getSeconds()<10){
                                    time+= '0' + currentdate.getSeconds()
                                } else{
                                    time+= currentdate.getSeconds()
                                }
                                let date = ''
                                if(currentdate.getDate()<10){
                                    date += '0' + currentdate.getDate() + ':'
                                } else{
                                    date += currentdate.getDate() + ':'
                                }
                                if(parseInt(currentdate.getMonth() + 1)<10){
                                    date += '0' + parseInt(currentdate.getMonth() + 1) + ':'
                                } else{
                                    date += parseInt(currentdate.getMonth() + 1) + ':'
                                }
                                date += currentdate.getFullYear()
                        let mess = {
                            user: user1,
                            message: req.body.text,
                            time: time,
                            date: date
            
                        }
                        couch.NewMessage(nameDB, mess, (b)=>{
                            io.to(user2.login + '_' + user1.login).emit('newMessage', mess)
                            console.log(user2.login + '_' + user1.login)
                            res.redirect('/messages/' + `${('<gl3_' + req.params.id).split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
                        })
                    
                })
            }
            
        })
        res.redirect(`${('<gl3_' + req.params.id).split('').map(char => String.fromCharCode(char.charCodeAt(0) + 16)).join('')}`)
    })
})

server.listen(PORT, () => { 
    console.log('Server is started') 
})