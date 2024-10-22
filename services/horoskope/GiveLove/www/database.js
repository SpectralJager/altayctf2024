const nodeCouchdb = require('node-couchdb')
const NodeCouchDb = require('node-couchdb')

let couch = new NodeCouchDb({
    host: 'db',
    protocol: 'http',
    port: 5984,
    auth: {
        user: 'admin',
        pass: 'admin'
    }
})

const GetUser = (userId, callback) => {
    couch.get('gl_users', userId).then(({ data, headers, status }) => {
        callback(data)
    }, (err) => {
        callback(false)
    })
}

const NewUser = (user, collback) => {
    if (user.name != '' && user.surname != '' && user.fathername != '' && user.date != '' &&
        user.login != '' && user.password != '' && user.password == user.passwordrepeat && (user.man == 'Мужской' || user.women == 'Женский'))
        var g
    if (user.man == 'Мужской')
        g = 'Мужской'
    else
        g = 'Женский'
    GetZZ(user.date, (zz) => {
        couch.insert('gl_users', {
            _id: '<gl3_' + user.login.toLowerCase(),
            login: user.login,
            password: user.password,
            name: user.name,
            surname: user.surname,
            fathername: user.fathername,
            date: user.date,
            gender: g,
            ZZ: zz,
            role: 'user',
            avatar: 'default.jpg'
        }).then(({ data, headers, status }) => {
            collback(true)
        }, (err)=>{callback(false)})
    })

}

const GenerateSession = (userId, callback) => {
    couch.insert('gl_sessions', {
        UserID: userId
    }).then(({ data, headers, status }) => {
        couch.get('gl_sessions', '_design/getSession/_view/getSession', { key: userId }).then(({ data, headers, status }) => {
            callback(data.rows[0].id)
        })
    })
}
const OpenSession = (_id, callback) => {
    couch.get('gl_sessions', _id).then(({ data, headers, status }) => {
        callback(true)
    }, (err) => { callback(false) })
}

const GetUserbySession = (_id, callback) => {
    couch.get('gl_sessions', _id).then(({ data, headers, status }) => {
        couch.get('gl_users', data.UserID).then(({ data, headers, status }) => {
            callback(data)
        })
    })
}
const DeleteUser = (user, callback) => {
    couch.del('gl_users', user._id, user._rev).then(({ data, headers, status }) => {
        callback(true)
    }, (err) => {
        callback(false)
    })
}

const UpdatePassword = (user, newPass, callback) => {
    couch.update('gl_users', {
        _id: user._id,
        _rev: user._rev,
        login: user.login,
        password: newPass,
        name: user.name,
        surname: user.surname,
        fathername: user.fathername,
        date: user.date,
        gender: user.gender,
        ZZ: user.ZZ,
        role: user.role,
        avatar: user.avatar
    }).then(({ data, headers, status }) => {
        callback(true)
    })
}

const UserUpdate = (user, newUser, callback) => {
    GetZZ(newUser.date, (zz) => {
        couch.update('gl_users', {
            _id: user._id,
            _rev: user._rev,
            login: user.login,
            password: user.password,
            name: newUser.name,
            surname: newUser.surname,
            fathername: newUser.fathername,
            date: newUser.date,
            gender: newUser.gender,
            ZZ: zz,
            role: user.role,
            avatar: user.avatar
        }).then(({ data, headers, status }) => {
            callback(true)
        })
    })
}

const AlienUserUpdate = (user, newUser, callback) => {
    GetZZ(newUser.date, (zz) => {
        couch.update('gl_users', {
            _id: user._id,
            _rev: user._rev,
            login: user.login,
            password: user.password,
            name: newUser.name,
            surname: newUser.surname,
            fathername: newUser.fathername,
            date: newUser.date,
            gender: newUser.gender,
            ZZ: zz,
            role: newUser.role,
            avatar: user.avatar
        }).then(({ data, headers, status }) => {
            callback(true)
        })
    })
}

const GetAllUsers = (callback) => {
    couch.get('gl_users', '_design/GetUsers/_view/GetAllUsers').then(({ data, headers, status }) => {
        callback(data)
    })
}

const GetNews = (zz, callback) => {
    couch.get('gl_horoscope', '_design/news/_view/new', { key: zz }).then(({ data, headers, status }) => {
        callback(data)
    })
}

const GetZZ = (date, callback) => {
    let str = date.split("-")
    if (str[1] == "03" && str[2] >= "21" || str[1] == "04" && str[2] <= "20")
        callback("aries")
    else if (str[1] == "04" && str[2] >= "21" || str[1] == "05" && str[2] <= "21")
        callback("taurus")
    else if (str[1] == "05" && str[2] >= "22" || str[1] == "06" && str[2] <= "21")
        callback("gemini")
    else if (str[1] == "06" && str[2] >= "22" || str[1] == "07" && str[2] <= "23")
        callback("canser")
    else if (str[1] == "07" && str[2] >= "24" || str[1] == "08" && str[2] <= "23")
        callback("leo")
    else if (str[1] == "08" && str[2] >= "24" || str[1] == "09" && str[2] <= "23")
        callback("virgo")
    else if (str[1] == "09" && str[2] >= "24" || str[1] == "10" && str[2] <= "23")
        callback("libra")
    else if (str[1] == "10" && str[2] >= "24" || str[1] == "11" && str[2] <= "22")
        callback("scorpio")
    else if (str[1] == "11" && str[2] >= "23" || str[1] == "12" && str[2] <= "21")
        callback("sagittarius")
    else if (str[1] == "12" && str[2] >= "22" || str[1] == "01" && str[2] <= "20")
        callback("capricorn")
    else if (str[1] == "01" && str[2] >= "21" || str[1] == "02" && str[2] <= "19")
        callback("aquarius")
    else if (str[1] == "02" && str[2] >= "20" || str[1] == "03" && str[2] <= "20")
        callback("virgo")
}

const AvatarUpdate = (user, avatar, callback) => {
    couch.update('gl_users', {
        _id: user._id,
        _rev: user._rev,
        login: user.login,
        password: user.password,
        name: user.name,
        surname: user.surname,
        fathername: user.fathername,
        date: user.date,
        gender: user.gender,
        ZZ: user.ZZ,
        role: user.role,
        avatar: avatar
    }).then(({ data, headers, status }) => {
        callback(true)
    })
}

const DeleteSession = (cookie, callback) => {
    couch.get('gl_sessions', cookie).then(({ data, headers, status }) => {
        console.log(data)
        couch.del('gl_sessions', data._id, data._rev).then(({ data, headers, status }) => {
            console.log('Ia tyt')
            callback(true)
        }, (err) => {
            callback(false)
        })
    }, (err) => {})
}

const CreateMessage = (user1, user2, callback)=>{
    let u = [user1.login, user2.login]
    u.sort()
    let nameDB = u[0] +  '_' + u[1] + '_' + "messages"
    nameDB = nameDB.toLowerCase()
    couch.createDatabase(nameDB.toLowerCase()).then(()=>{
        couch.insert(nameDB,{
            _id: "_design/GetMessages",
  views: {
    GetMessages: {
      map: "function (doc) {\n  emit([doc.date, doc.time], {\n    user:doc.user,\n    message:doc.message,\n    time:doc.time,\n    date:doc.date\n  });\n}"
    }
  },
  language: "javascript"
        })
        callback(true)
    }, 
    err=>{
        callback(true)
    })
}

const NewMessage = (DB, message, callback)=>{
    DB = DB.toLowerCase()
    couch.insert(DB, message
        ).then(({data, headers, status }) => {
        callback(true)
    }, (err) => {})
}

const GetMessages = (DB, callback)=>{
    DB = DB.toLowerCase()
    couch.get(DB, '_design/GetMessages/_view/GetMessages').then(({ data, headers, status }) => {
        callback(data)
    }, (err)=>{
        let b = false
        callback(b)
    })
}

module.exports = {
    GetUser: GetUser,
    NewUser: NewUser,
    GenerateSession: GenerateSession,
    OpenSession: OpenSession,
    GetUserbySession: GetUserbySession,
    DeleteUser: DeleteUser,
    UpdatePassword: UpdatePassword,
    UserUpdate: UserUpdate,
    GetAllUsers: GetAllUsers,
    GetNews: GetNews,
    AlienUserUpdate: AlienUserUpdate,
    AvatarUpdate: AvatarUpdate,
    DeleteSession: DeleteSession,
    CreateMessage: CreateMessage,
    NewMessage: NewMessage,
    GetMessages: GetMessages
}
